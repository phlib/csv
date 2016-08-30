<?php
namespace Phlib\Csv\Adapter;

interface AdapterInterface
{
    public function getStream();
    public function closeStream();
}