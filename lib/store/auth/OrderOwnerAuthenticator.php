<?php
include_once("auth/UserAuthenticator.php");
include_once("store/beans/OrdersBean.php");

class OrderOwnerAuthenticator extends UserAuthenticator
{

    public function authorize(array $user_data = NULL) : ?AuthContext
    {

        $context = parent::authorize($user_data);

        if ($context != NULL) {

            $logged_userID = $context->getID();

            $orders = new OrdersBean();
            $orderID = (int)$user_data[$orders->key()];

            $order_userID = (int)$orders->getValue($orderID, "userID");

            if ($logged_userID == $order_userID) {
                debug("Authenticated userID is matching the order ownerID");
                return $context;
            }
            debug("Authorized but not owner of this order");

            return NULL;

        }

        debug("Not authorized");

        return $context;

    }
}

?>
