<?php

namespace App\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait TimestampableEntity
{
  /**
   * @var \DateTime
   * @Gedmo\Timestampable(on="create")
   * 
   * Nullable to avoid validations
   * @ORM\Column(type="datetime", nullable=true)
   */
  #[Gedmo\Timestampable(on: 'create')]
  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected $createdAt;

  /**
   * @var \DateTime
   * @Gedmo\Timestampable(on="update")
   * 
   * Nullable to avoid validations
   * @ORM\Column(type="datetime", nullable=true)
   */
  #[Gedmo\Timestampable(on: 'update')]
  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected $updatedAt;

  /**
   * Sets createdAt.
   *
   * @return $this
   */
  public function setCreatedAt(\DateTime $createdAt)
  {
    $this->createdAt = $createdAt;

    return $this;
  }

  /**
   * Returns createdAt.
   *
   * @return \DateTime
   */
  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  /**
   * Sets updatedAt.
   *
   * @return $this
   */
  public function setUpdatedAt(\DateTime $updatedAt)
  {
    $this->updatedAt = $updatedAt;

    return $this;
  }

  /**
   * Returns updatedAt.
   *
   * @return \DateTime
   */
  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }
}
