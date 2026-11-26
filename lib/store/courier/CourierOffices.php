<?php
include_once("store/courier/ICourierOffices.php");
include_once("store/courier/Couriers.php");
include_once("store/beans/CourierOfficesBean.php");
include_once("store/beans/CourierAddressesBean.php");
include_once("store/utils/cart/DeliveryCourier.php");
include_once("dbdriver/DBManager.php");
include_once("objects/OrderColumn.php");

/**
 * The offices of the couriers: the local copy (CourierOfficesBean), its refresh from the courier APIs, the office
 * a client chose (CourierAddressesBean - one per courier) and its text for the order.
 */
class CourierOffices
{
    /**
     * The API of a courier - null for a courier without one
     */
    public static function Source(int $courier) : ?ICourierOffices
    {
        return Couriers::Get($courier)?->offices();
    }

    /**
     * Replace the copy of the offices of '$courier' with the courier's list - in one transaction, only when the
     * courier answered with offices (a failed or empty answer keeps the old copy)
     * @return int the offices stored
     * @throws Exception
     */
    public static function Sync(int $courier, ?ICourierOffices $source = null) : int
    {
        $source = $source ?? CourierOffices::Source($courier);
        if (is_null($source)) throw new Exception("No office list for courier $courier");

        $offices = $source->fetch();
        if (count($offices) < 1) throw new Exception("The courier sent no offices - the old list is kept");

        $bean = new CourierOfficesBean();
        $db = DBManager::Driver();
        try {
            $db->transaction();
            $bean->deleteRef("courier", (string)$courier, $db);
            $seen = array();
            foreach ($offices as $office) {
                $key = (string)$office["office_key"];
                if ($key === "" || isset($seen[$key])) continue;
                $seen[$key] = true;
                $bean->insert(array(
                    "courier" => $courier,
                    "office_key" => mb_substr($key, 0, 32),
                    "city" => mb_substr((string)$office["city"], 0, 128),
                    "post_code" => mb_substr((string)$office["post_code"], 0, 16),
                    "name" => mb_substr((string)$office["name"], 0, 255),
                    "address" => mb_substr((string)$office["address"], 0, 255),
                    "cash" => 1,
                ), $db);
            }
            $db->commit();
        }
        catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
        return count($seen);
    }

    /**
     * The office '$key' of '$courier' in the copy, or null
     */
    public static function Find(int $courier, string $key) : ?array
    {
        if ($key === "") return null;
        $bean = new CourierOfficesBean();
        $query = $bean->query("office_key", "city", "post_code", "name", "address");
        $query->stmt->where()->match("courier", $courier);
        $query->stmt->where()->match("office_key", $key);
        $query->stmt->limit(1);
        $query->exec();
        $row = $query->next();
        $query->free();
        return is_array($row) ? $row : null;
    }

    /**
     * The text of an office for the order and the mails: 'Хасково - Склад, Източна индустриална зона, 6300 Хасково'
     */
    public static function Text(array $office) : string
    {
        $place = trim(trim((string)$office["post_code"]) . " " . trim((string)$office["city"]));
        $parts = array_filter(array(trim((string)$office["name"]), trim((string)$office["address"]), $place), fn($part) => $part !== "");
        return implode(", ", $parts);
    }

    /**
     * The office of '$courier' the client chose, when it is still in the copy - the office row plus 'office' (its
     * text) - else null (an office typed before the office list, or closed since, is chosen again)
     */
    public static function Chosen(int $userID, int $courier) : ?array
    {
        $bean = new CourierAddressesBean();
        $query = $bean->query("office_key");
        $query->stmt->where()->match("userID", $userID);
        $query->stmt->where()->match("courier", $courier);
        $query->stmt->limit(1);
        $query->exec();
        $row = $query->next();
        $query->free();
        if (!is_array($row)) return null;

        $office = CourierOffices::Find($courier, (string)$row["office_key"]);
        if (is_null($office)) return null;
        return $office + array("office" => CourierOffices::Text($office));
    }

    /**
     * Keep the office the client chose for '$courier' (one per courier)
     */
    public static function Choose(int $userID, int $courier, array $office) : void
    {
        $bean = new CourierAddressesBean();
        $row = array("office" => mb_substr(CourierOffices::Text($office), 0, 255), "office_key" => (string)$office["office_key"]);

        $query = $bean->query($bean->key());
        $query->stmt->where()->match("userID", $userID);
        $query->stmt->where()->match("courier", $courier);
        $query->stmt->limit(1);
        $query->exec();
        $existing = $query->next();
        $query->free();

        if (is_array($existing)) {
            $bean->update((int)$existing[$bean->key()], $row);
        }
        else {
            $bean->insert($row + array("userID" => $userID, "courier" => $courier));
        }
    }

    /**
     * The offices of '$courier' for the OptionFinder: key, title (name), text (address, post code and city),
     * prefix (the city, lower case - matched from its start); the finder searches title and text too. Ordered by city.
     * @return array<int, array<string, string>>
     */
    public static function Options(int $courier) : array
    {
        $bean = new CourierOfficesBean();
        $query = $bean->query("office_key", "city", "post_code", "name", "address");
        $query->stmt->where()->match("courier", $courier);
        $query->stmt->order("city", OrderDirection::ASC);
        $query->stmt->order("name", OrderDirection::ASC);
        $query->exec();
        $options = array();
        while ($row = $query->next()) {
            $text = implode(", ", array_filter(array(trim((string)$row["address"]), trim($row["post_code"] . " " . $row["city"])), fn($part) => $part !== ""));
            $options[] = array(
                "key" => (string)$row["office_key"],
                "title" => (string)$row["name"],
                "text" => $text,
                "prefix" => mb_strtolower((string)$row["city"]),
            );
        }
        $query->free();
        return $options;
    }

    /**
     * The count and the last refresh of the copy of '$courier': array('count' => int, 'updated' => string)
     */
    public static function Status(int $courier) : array
    {
        $bean = new CourierOfficesBean();
        $query = $bean->query($bean->key());
        $query->stmt->columns()->reset();
        $query->stmt->alias("COUNT(*)", "cnt");
        $query->stmt->alias("MAX(updated)", "last");
        $query->stmt->where()->match("courier", $courier);
        $query->exec();
        $row = $query->next() ?: array();
        $query->free();
        return array("count" => (int)($row["cnt"] ?? 0), "updated" => (string)($row["last"] ?? ""));
    }
}
