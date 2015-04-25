<?php
namespace DQNEO\Fluent;

class Exception extends \Exception
{
    protected $entity;

    /**
     * @param                 $tag
     * @param                 $data
     * @param string          $message
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(Entity $entity, $message = "", $code = 0, \Exception $previous = null)
    {
        $this->entity = $entity;
        parent::__construct($message, $code, $previous);
    }

    /**
     * get entity
     *
     * @return string $tag
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
