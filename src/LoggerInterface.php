<?php
namespace DQNEO\Fluent;

interface LoggerInterface
{
    /**
     * @abstract
     * @param string $tag
     * @param array  $data
     *
     * @api
     */
    public function post($tag, array $data);

}
