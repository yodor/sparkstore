<?php
include_once("auth/UserAuthenticator.php");
include_once("store/beans/OrdersBean.php");

/**
 * The photos of an order (order_items: auth_context 'OrderOwnerAuthenticator' - BeanDataResponse) for the client who
 * placed the order only (an administrator sees them anyway - Authenticator::AuthorizeResource)
 */
class OrderOwnerAuthenticator extends UserAuthenticator
{

    public function authorize(?array $user_data = NULL) : ?AuthContext
    {

        $context = parent::authorize($user_data);

        if ($context != NULL) {

            $logged_userID = $context->getID();

            $orders = new OrdersBean();
            $orderID = (int)($user_data[$orders->key()] ?? 0);
            if ($orderID < 1) {
                Debug::ErrorLog("No order of the resource");
                return NULL;
            }

            $order_userID = (int)$orders->getValue($orderID, "userID");

            if ($logged_userID > 0 && $logged_userID == $order_userID) {
                Debug::ErrorLog("Authenticated userID is matching the order ownerID");
                return $context;
            }
            Debug::ErrorLog("Authorized but not owner of this order");

            return NULL;

        }

        Debug::ErrorLog("Not authorized");

        return $context;

    }
}