<?php

namespace Blockonomics\Merchant\Controller\Sendmail;

class Sendmail extends \Magento\Framework\App\Action\Action
{
    /**
    * Recipient email config path
    */
    const XML_PATH_EMAIL_RECIPIENT = 'test/email/send_email';
    /**
    * Sender email config path - from default CONTACT extension
    */
    const XML_PATH_EMAIL_SENDER = 'contact/email/sender_email_identity';
    /**
    * @var \Magento\Framework\Mail\Template\TransportBuilder
    */
    protected $_transportBuilder;

    /**
    * @var \Magento\Framework\Translate\Inline\StateInterface
    */
    protected $inlineTranslation;

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;
    /**
    * @var \Magento\Framework\Escaper
    */
    protected $_escaper;
    /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
    \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\Escaper $escaper
    ) {
    parent::__construct($context);
    $this->_transportBuilder = $transportBuilder;
    $this->inlineTranslation = $inlineTranslation;
    $this->scopeConfig = $scopeConfig;
    $this->storeManager = $storeManager;
    $this->_escaper = $escaper;
    }
    /**
    * Post user question
    *
    * @return void
    * @throws \Exception
    */
    public function execute()
    {
    // $post = $this->getRequest()->getPost();
    if (!$this->getRequest()->getParam('id')) {
        $response = $this->resultFactory
        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
        ->setData([
            'status'  => "fail",
            'error' => "no id"
        ]);
        return $response;
    }else{
            $post['id'] = $this->getRequest()->getParam('id');
            $post['uuid'] = $this->getRequest()->getParam('uuid');
            $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->load($post['id']);
            $email = $order->getCustomerEmail();
            $name = $order->getCustomerName();
            $post['link'] = $this->getSystemUrl() . 'blockonomics/payment/status?uuid=' . $post['uuid'];
            // $post['name'] = "Darren";
            // $post['email'] = "darrenwestwood86@yahoo.com";
            $this->inlineTranslation->suspend();
            try {
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($post);
                // $sender = [
                // 'name' => $this->_escaper->escapeHtml($post['name']),
                // 'email' => $this->_escaper->escapeHtml($post['email']),
                // ]; 
                $error = false;

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $transport = $this->_transportBuilder
                ->setTemplateIdentifier('blockonomics_email_template') // this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($this->emailSender())
                ->addTo($email, $name)
                ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
                $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => "ok",
                    'message' => 'email sent'
                ]);
                return $response;
            } catch (\Exception $e) {
                $this->inlineTranslation->resume();
                $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => "fail",
                    'error' => $e->getMessage()
                ]);
                return $response;
            }
        }

    }

    /**
    * Return email for sender header
    * @return mixed
    */
    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SENDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getSystemUrl() {
        return $this->storeManager->getStore()->getBaseUrl();
    }
}