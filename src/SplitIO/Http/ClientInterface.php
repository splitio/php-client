<?php
namespace SplitIO\Http;

interface ClientInterface
{


    public function get($path, $data = false);
    public function post($path, $data);
    //public function put($path, $data);
    //public function patch($path, $data);
    //public function delete($path, $data);
}