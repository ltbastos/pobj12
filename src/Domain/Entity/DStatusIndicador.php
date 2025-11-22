<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="d_status_indicadores")
 */
class DStatusIndicador
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=20)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $status;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}

