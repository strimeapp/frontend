<?php

    namespace Strime\GlobalBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="app_reset_pwd_token")
     */
    class ResetPwdToken
    {

        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="token", type="string", length=20, nullable=false)
         */
        protected $token;

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
         * @param string $token
         * @return ResetPwdToken
         */
        public function setToken($token) {
            $this->token = $token;
            return $this;
        }

        /**
         * @return string
         */
        public function getToken() {
            return $this->token;
        }

        /**
         * @param string $user_id
         * @return ResetPwdToken
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
         * @return ResetPwdToken
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
