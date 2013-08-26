<?php

namespace PerunWs\User;


class Storage implements StorageInterface
{


    public function fetch($id)
    {
        return array(
            'id' => $id
        );
    }


    public function fetchAll()
    {
        return array(
            array(
                'id' => 111
            )
        );
    }
}