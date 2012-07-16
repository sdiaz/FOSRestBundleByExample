<?php

/**
 * This file is part of the FOSRestByExample package.
 *
 * (c) Santiago Diaz <santiago.diaz@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace ByExample\DemoBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity that persists the user information
 *
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 *
 */
class User extends BaseUser
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="ByExample\DemoBundle\Entity\Portal")
     * @ORM\JoinTable(name="rel_fosuser_portal",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="portal_id", referencedColumnName="id", unique=true)}
     * )
     **/
    protected $portal = null;


    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->portal = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add portal
     *
     * @param ByExample\DemoBundle\Entity\Portal $portal Portal
     *
     * @return User
     */
    public function addPortal(\ByExample\DemoBundle\Entity\Portal $portal)
    {
        $this->portal[] = $portal;
        return $this;
    }

    /**
     * Get portal
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getPortal()
    {
        return $this->portal;
    }
}