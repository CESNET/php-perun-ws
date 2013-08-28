<?php

namespace PerunWs\Group\Service;


interface ServiceInterface
{


    public function fetch($id);


    public function fetchAll();


    public function create($data);


    public function patch($id, $data);


    public function delete($id);
}