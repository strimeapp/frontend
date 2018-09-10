<?php

    namespace Strime\GlobalBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="app_new_feature")
     */
    class NewFeature
    {

        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="secret_id", type="string", length=10, nullable=false)
         */
        protected $secret_id;

        /**
         * @ORM\Column(name="title_fr", type="string", length=255, nullable=false)
         */
        protected $title_fr;

        /**
         * @ORM\Column(name="title_en", type="string", length=255, nullable=false)
         */
        protected $title_en;

        /**
         * @ORM\Column(name="title_es", type="string", length=255, nullable=false)
         */
        protected $title_es;

        /**
         * @ORM\Column(name="description_fr", type="text", nullable=false)
         */
        protected $description_fr;

        /**
         * @ORM\Column(name="description_en", type="text", nullable=false)
         */
        protected $description_en;

        /**
         * @ORM\Column(name="description_es", type="text", nullable=false)
         */
        protected $description_es;

        /**
         * @ORM\Column(name="blog_url_fr", type="string", length=255, nullable=false)
         */
        protected $blog_url_fr;

        /**
         * @ORM\Column(name="blog_url_en", type="string", length=255, nullable=false)
         */
        protected $blog_url_en;

        /**
         * @ORM\Column(name="blog_url_es", type="string", length=255, nullable=false)
         */
        protected $blog_url_es;

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
         * @param string $secret_id
         * @return NewFeature
         */
        public function setSecretId($secret_id) {
            $this->secret_id = $secret_id;
            return $this;
        }

        /**
         * @return string
         */
        public function getSecretId() {
            return $this->secret_id;
        }

        /**
         * @param string $title_fr
         * @return NewFeature
         */
        public function setTitleFr($title_fr) {
            $this->title_fr = $title_fr;
            return $this;
        }

        /**
         * @return string
         */
        public function getTitleFr() {
            return $this->title_fr;
        }

        /**
         * @param string $title_en
         * @return NewFeature
         */
        public function setTitleEn($title_en) {
            $this->title_en = $title_en;
            return $this;
        }

        /**
         * @return string
         */
        public function getTitleEn() {
            return $this->title_en;
        }

        /**
         * @param string $title_es
         * @return NewFeature
         */
        public function setTitleEs($title_es) {
            $this->title_es = $title_es;
            return $this;
        }

        /**
         * @return string
         */
        public function getTitleEs() {
            return $this->title_es;
        }

        /**
         * @param string $description_fr
         * @return NewFeature
         */
        public function setDescriptionFr($description_fr) {
            $this->description_fr = $description_fr;
            return $this;
        }

        /**
         * @return string
         */
        public function getDescriptionFr() {
            return $this->description_fr;
        }

        /**
         * @param string $description_en
         * @return NewFeature
         */
        public function setDescriptionEn($description_en) {
            $this->description_en = $description_en;
            return $this;
        }

        /**
         * @return string
         */
        public function getDescriptionEn() {
            return $this->description_en;
        }

        /**
         * @param string $description_es
         * @return NewFeature
         */
        public function setDescriptionEs($description_es) {
            $this->description_es = $description_es;
            return $this;
        }

        /**
         * @return string
         */
        public function getDescriptionEs() {
            return $this->description_es;
        }

        /**
         * @param string $blog_url_fr
         * @return NewFeature
         */
        public function setBlogUrlFr($blog_url_fr) {
            $this->blog_url_fr = $blog_url_fr;
            return $this;
        }

        /**
         * @return string
         */
        public function getBlogUrlFr() {
            return $this->blog_url_fr;
        }

        /**
         * @param string $blog_url_en
         * @return NewFeature
         */
        public function setBlogUrlEn($blog_url_en) {
            $this->blog_url_en = $blog_url_en;
            return $this;
        }

        /**
         * @return string
         */
        public function getBlogUrlEn() {
            return $this->blog_url_en;
        }

        /**
         * @param string $blog_url_es
         * @return NewFeature
         */
        public function setBlogUrlEs($blog_url_es) {
            $this->blog_url_es = $blog_url_es;
            return $this;
        }

        /**
         * @return string
         */
        public function getBlogUrlEs() {
            return $this->blog_url_es;
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
         * @param \DateTime $updated_at
         * @return NewFeature
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
