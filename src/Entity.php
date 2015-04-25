<?php
namespace DQNEO\Fluent;

class Entity
{
    /* @var int unxitime */
    public $time;

    /* @var string Fluentd tag */
    public $tag;

    /* @var array structured log data */
    public $data = array();

    /**
     * create a entity for sending to fluentd server
     *
     * @param     $tag
     * @param     $data
     * @param int $time unixtime
     */
    public function __construct($tag, $data, $time = null)
    {
        if (is_long($time)) {
            $this->time = $time;
        } else {
            if (!is_null($time)) {
                error_log("Entity::__construct(): unexpected time format `{$time}` specified.");
            }

            $this->time = time();
        }

        $this->tag  = $tag;
        $this->data = $data;
    }

    /**
     * get current tag
     *
     * @return string tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * get current data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * get current unixtime
     *
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }
}
