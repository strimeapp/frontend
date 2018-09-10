<?php

    namespace Strime\GlobalBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="app_upload")
     */
    class Upload
    {

        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="user_id", type="string", length=20, nullable=false)
         */
        protected $user_id;

        /**
         * @ORM\Column(name="created_at", type="datetime")
         */
        protected $created_at;

        /**
         * @ORM\Column(name="updated_at", type="datetime")
         */
        protected $updated_at;



        /**
         * @return integer
         */
        public function getId() {
            return $this->id;
        }

        /**
         * @param string $user_id
         * @return Upload
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
         * @return Upload
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
         * @param \DateTime $updated_at
         * @return Upload
         */
        public function setUpdatedAt($updated_at) {
            $this->updated_at = $updated_at;
            return $this;
        }

        /**
         * @return \DateTime
         */
        public function getUpdatedAt() {
            return $this->updated_at;
        }

        /**
         * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
         *
         * @ORM\PrePersist
         * @ORM\PreUpdate
         */
        public function updatedTimestamps() {
            $this->setUpdatedAt(new \DateTime(date('Y-m-d H:i:s')));

            if($this->getCreatedAt() == null)
            {
                $this->setCreatedAt(new \DateTime(date('Y-m-d H:i:s')));
            }
        }
    }
