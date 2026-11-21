<?php

/**
 * The offices of one courier, read from its API (CourierOffices::Sync keeps a local copy).
 * fetch() returns only offices (no lockers) accepting cash on delivery, each as
 *   array('office_key' => string, 'city' => string, 'post_code' => string, 'name' => string, 'address' => string)
 * and throws when the courier does not answer (the old copy is kept).
 */
interface ICourierOffices
{
    /**
     * @return array<int, array<string, string>>
     * @throws Exception
     */
    public function fetch() : array;
}
