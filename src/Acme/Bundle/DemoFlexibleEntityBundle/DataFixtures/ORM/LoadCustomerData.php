<?php
namespace Acme\Bundle\DemoFlexibleEntityBundle\DataFixtures\ORM;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
* Load customers
*
* Execute with "php app/console doctrine:fixtures:load"
*
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
*
*/
class LoadCustomerData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Flexible entity manager
     * @var FlexibleEntityManager
     */
    protected $manager;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get product manager
     * @return SimpleEntityManager
     */
    protected function getCustomerManager()
    {
        return $this->container->get('customer_manager');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadAttributes();
        $this->loadCustomers();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 3;
    }

    /**
     * Load attributes
     * @return array()
     */
    public function loadAttributes()
    {
        $messages = array();
        // force in english
        $this->getCustomerManager()->setLocale('en_US');

        // attribute company (if not exists)
        $attCode = 'company';
        $att = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->createAttribute();
            $att->setCode($attCode);
//            $att->setBackendType(AbstractAttributeType::BACKEND_TYPE_VARCHAR);
            $att->setFrontendType(AbstractAttributeType::FRONTEND_TYPE_TEXTFIELD);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        // attribute date of birth (if not exists)
        $attCode = 'dob';
        $att = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->createAttribute();
            $att->setCode($attCode);
//            $att->setBackendType(AbstractAttributeType::BACKEND_TYPE_DATE);
            $att->setFrontendType(AbstractAttributeType::FRONTEND_TYPE_DATE);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        // attribute gender (if not exists)
        $attCode = 'gender';
        $att = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode($attCode);
        if ($att) {
            $messages[]= "Attribute ".$attCode." already exists";
        } else {
            $att = $this->getCustomerManager()->createAttribute();
            $att->setCode($attCode);
//            $att->setBackendType(AbstractAttributeType::BACKEND_TYPE_OPTION);
            $att->setFrontendType(AbstractAttributeType::FRONTEND_TYPE_LIST);
            // add option and related value
            $opt = $this->getCustomerManager()->createAttributeOption();
            $optVal = $this->getCustomerManager()->createAttributeOptionValue();
            $optVal->setValue('Mr');
            $opt->addOptionValue($optVal);
            $att->addOption($opt);
            // add another option
            $opt = $this->getCustomerManager()->createAttributeOption();
            $optVal = $this->getCustomerManager()->createAttributeOptionValue();
            $optVal->setValue('Mrs');
            $opt->addOptionValue($optVal);
            $att->addOption($opt);
            $this->getCustomerManager()->getStorageManager()->persist($att);
            $messages[]= "Attribute ".$attCode." has been created";
        }

        $this->getCustomerManager()->getStorageManager()->flush();

        return $messages;
    }

    /**
     * Load customers
     * @return array
     */
    public function loadCustomers()
    {
        $messages = array();

        // get attributes
        $attCompany = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode('company');
        $attDob = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode('dob');
        $attGender = $this->getCustomerManager()->getEntityRepository()->findAttributeByCode('gender');
        // get first attribute option
        $optGender = $this->getCustomerManager()->getAttributeOptionRepository()->findOneBy(array('attribute' => $attGender));

        for ($ind= 1; $ind < 100; $ind++) {

            // add customer with email, firstname, lastname, dob
            $custEmail = 'email-'.($ind++).'@mail.com';
            $customer = $this->getCustomerManager()->getEntityRepository()->findOneByEmail($custEmail);
            if ($customer) {
                $messages[]= "Customer ".$custEmail." already exists";
            } else {
                $customer = $this->getCustomerManager()->createEntity();
                $customer->setEmail($custEmail);
                $customer->setFirstname($this->generateFirstname());
                $customer->setLastname($this->generateLastname());
                // add dob value
                if ($attCompany) {
                    $value = $this->getCustomerManager()->createEntityValue();
                    $value->setAttribute($attDob);
                    $value->setData(new \DateTime($this->generateBirthDate()));
                    $customer->addValue($value);
                }
                $messages[]= "Customer ".$custEmail." has been created";
                $this->getCustomerManager()->getStorageManager()->persist($customer);
            }

            // add customer with email, firstname, lastname, company and gender
            $custEmail = 'email-'.($ind++).'@mail.com';
            $customer = $this->getCustomerManager()->getEntityRepository()->findOneByEmail($custEmail);
            if ($customer) {
                $messages[]= "Customer ".$custEmail." already exists";
            } else {
                $customer = $this->getCustomerManager()->createEntity();
                $customer->setEmail($custEmail);
                $customer->setFirstname($this->generateFirstname());
                $customer->setLastname($this->generateLastname());
                // add company value
                if ($attCompany) {
                    $value = $this->getCustomerManager()->createEntityValue();
                    $value->setAttribute($attCompany);
                    $value->setData('Akeneo');
                    $customer->addValue($value);
                }
                // add date of birth
                if ($attDob) {
                    $value = $this->getCustomerManager()->createEntityValue();
                    $value->setAttribute($attDob);
                    $value->setData(new \DateTime($this->generateBirthDate()));
                    $customer->addValue($value);
                }
                // add gender
                if ($attGender) {
                    $value = $this->getCustomerManager()->createEntityValue();
                    $value->setAttribute($attGender);
                    $value->setOption($optGender);  // we set option as data, you can use $value->setOption($optGender) too
                    $customer->addValue($value);
                }
                $messages[]= "Customer ".$custEmail." has been created";
                $this->getCustomerManager()->getStorageManager()->persist($customer);
            }
        }

        $this->getCustomerManager()->getStorageManager()->flush();

        return $messages;
    }

    /**
     * Generate firstname
     * @return string
     */
    protected function generateFirstname()
    {
        $listFirstname = array('Nicolas', 'Romain');
        $random = rand(0, count($listFirstname)-1);

        return $listFirstname[$random];
    }

    /**
     * Generate lastname
     * @return string
     */
    protected function generateLastname()
    {
        $listLastname = array('Dupont', 'Monceau');
        $random = rand(0, count($listLastname)-1);

        return $listLastname[$random];
    }

    /**
     * Generate birthdate
     * @return string
     */
    protected function generateBirthDate()
    {
        $year  = rand(1980, 2000);
        $month = rand(1, 12);
        $day   = rand(1, 28);

        return $year .'-'. $month .'-'. $day;
    }
}