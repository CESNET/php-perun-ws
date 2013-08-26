<?php

namespace PerunWs\User;


interface StorageInterface
{


    public function fetchAll();


    public function fetch($id);
}
