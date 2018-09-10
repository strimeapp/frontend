<?php

    namespace Strime\GlobalBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="app_has_read_new_feature")
     */
    class HasReadNewFeature
    {
        
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\ManyToOne(targetEntity="Strime\GlobalBundle\Entity\NewFeature")
         */
        protected $new_feature;

        /**
         * @ORM\Column(name="user_id", type="string", length=20, nullable=false)
         */
        protected $user_id;

        /**
         * @ORM\Column(name="created_at", type="datetime")
         */
        protected $created_at;



        /**
         * @return integer
         */
        public function getId() {
            return $this->id;
        }

        /**
         * @param integer $new_feature
         * @return HasReadNewFeature
         */
        public function setNewFeature($new_feature) {
            $this->new_feature = $new_feature;
            return $this;
        }

        /**
         * @return integer
         */
        public function getNewFeature() {
            return $this->new_feature;
        }

        /**
         * @param string $user_id
         * @return HasReadNewFeature
         */
        public function setUserId($user_id) {
            $this->user_id = $user_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getUserId() {
            return $this->user_id;
        }

        /**
         * @param \DateTime $created_at
         * @return NewFeature
         */
        public function setCreatedAt($created_at) {
            $this->created_at = $created_at;
            return $this;
        }

        /**
         * @return \DateTime
         */
        public function getCreatedAt() {
            return $this->created_at;
        }

        /**
         * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
         *
         * @ORM\PrePersist
         * @ORM\PreUpdate
         */
        public function updatedTimestamps() {

            if($this->getCreatedAt() == null)
            {
                $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
            }
        }
    }