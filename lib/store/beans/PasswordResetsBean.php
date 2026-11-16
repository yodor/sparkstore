<?php
include_once("beans/DBTableBean.php");

/**
 * One-time password reset links of the clients. Only the sha256 of the token is stored - the token is only in
 * the link of the mail. One link per client: a new one replaces the old one, using it removes it.
 * Times are unix timestamps (PHP time()).
 */
class PasswordResetsBean extends DBTableBean
{
    /**
     * Link lifetime in seconds
     */
    const int LIFETIME = 3600;

    /**
     * No new link (and mail) for this many seconds after the last one
     */
    const int THROTTLE = 120;

    protected string $createString = "
CREATE TABLE `password_resets` (
 `prID` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `userID` int(11) unsigned NOT NULL,
 `token_hash` char(64) NOT NULL,
 `created` int(11) unsigned NOT NULL,
 `expires` int(11) unsigned NOT NULL,
 PRIMARY KEY (`prID`),
 UNIQUE KEY `token_hash` (`token_hash`),
 UNIQUE KEY `userID` (`userID`),
 CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";

    public function __construct()
    {
        parent::__construct("password_resets");
    }

    /**
     * New link token of the client (replaces an older one).
     * Null when the last link was created less than THROTTLE seconds ago.
     * @param int $userID
     * @return string|null the token for the link
     * @throws Exception
     */
    public function create(int $userID) : ?string
    {
        $last = $this->getResult("userID", (string)$userID, "created");
        if (is_array($last) && (time() - (int)$last["created"]) < PasswordResetsBean::THROTTLE) return null;

        $this->deleteRef("userID", (string)$userID);

        $token = bin2hex(random_bytes(32));
        $row = array(
            "userID" => $userID,
            "token_hash" => hash("sha256", $token),
            "created" => time(),
            "expires" => time() + PasswordResetsBean::LIFETIME,
        );
        if ($this->insert($row) < 1) throw new Exception("Unable to store the reset link: " . $this->getDB()->getError());
        return $token;
    }

    /**
     * The client of a valid (not expired) link token, -1 otherwise
     * @param string $token
     * @return int
     * @throws Exception
     */
    public function userID(string $token) : int
    {
        if (!preg_match("/^[0-9a-f]{64}$/", $token)) return -1;
        $row = $this->getResult("token_hash", hash("sha256", $token), "userID", "expires");
        if (!is_array($row)) return -1;
        if ((int)$row["expires"] < time()) return -1;
        return (int)$row["userID"];
    }

    /**
     * Remove the links of the client (the link was used)
     * @param int $userID
     * @return void
     * @throws Exception
     */
    public function clear(int $userID) : void
    {
        $this->deleteRef("userID", (string)$userID);
    }
}
