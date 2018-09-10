<?php

    namespace Strime\GlobalBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
 	 * @ORM\HasLifecycleCallbacks
     * @ORM\Table(name="app_tax_rate")
     */
    class TaxRate
    {

        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        protected $id;

        /**
         * @ORM\Column(name="country", type="string", length=20, nullable=false)
         */
        protected $country;

        /**
         * @ORM\Column(name="country_code", type="string", length=2, nullable=false)
         */
        protected $country_code;

        /**
         * @ORM\Column(name="tax_rate", type="float", length=2, nullable=false)
         */
        protected $tax_rate;

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
         * @param string $country
         * @return TaxRate
         */
        public function setCountry($country) {
            $this->country = $country;
            return $this;
        }

        /**
         * @return string
         */
        public function getCountry() {
            return $this->country;
        }

        /**
         * @param string $country_code
         * @return TaxRate
         */
        public function setCountryCode($country_code) {
            $this->country_code = $country_code;
            return $this;
        }

        /**
         * @return string
         */
        public function getCountryCode() {
            return $this->country_code;
        }

        /**
         * @param float $tax_rate
         * @return TaxRate
         */
        public function setTaxRate($tax_rate) {
            $this->tax_rate = $tax_rate;
            return $this;
        }

        /**
         * @return float
         */
        public function getTaxRate() {
            return $this->tax_rate;
        }

        /**
         * @param \DateTime $created_at
         * @return TaxRate
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
         * @return TaxRate
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
