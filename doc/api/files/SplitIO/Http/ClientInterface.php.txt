<?php
namespace SplitIO\Http;

interface ClientInterface
{
    public function send(Request $request);
}
