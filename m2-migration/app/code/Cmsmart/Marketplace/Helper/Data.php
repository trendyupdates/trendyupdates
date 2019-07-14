<?php
/**
 * @copyright Copyright (c) 2016 www.cmsmart.net
 */

namespace Cmsmart\Marketplace\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
	const ATTRIBUTE_SET_ID = 'cmsmart_mp/product_settings/attributesetid';
	const ALLOWED_PRODUCT_TYPE = 'cmsmart_mp/product_settings/allow_product_type';
	const ALLOWED_SKU_TYPE = 'cmsmart_mp/product_settings/sku_type';
	const ALLOWED_SKU_PREFIX = 'cmsmart_mp/product_settings/sku_prefix';
	const PRODUCT_EDIT_APPROVAL = 'cmsmart_mp/product_settings/product_edit_approval';
	const TERM_AND_CONDITION = 'cmsmart_mp/account/term_and_condition';
	const PRODUCT_APPROVAL = 'cmsmart_mp/product_settings/product_approval';
	const ORDER_APPROVAL = 'cmsmart_mp/order_settings/order_approval';
	const ADMIN_EMAIL_ID = 'cmsmart_mp/store_settings/adminemail';
	const PRODUCT_EMAIL_TEMPLATE = 'cmsmart_mp/email_setting/product_email_template';
	const ACCOUNT_EMAIL_TEMPLATE = 'cmsmart_mp/email_setting/account_email_template';
	const CONTACT_EMAIL_TEMPLATE = 'cmsmart_mp/email_setting/contact_email_template';
	const SELLER_EMAIL_TEMPLATE = 'cmsmart_mp/email_setting/seller_email_template';
	const ADMIN_PRODUCT_EMAIL_TEMPLATE = 'cmsmart_mp/email_setting/admin_product_email_template';
	const PAY_SELLER_EMAIL_TEMPLATE = 'cmsmart_mp/email_setting/pay_seller_email_template';
	const ADMIN_LANDING_PAGE = 'cmsmart_mp/landing_page/layout_type';
	const ADMIN_LANDINGPAGE_HOTSELLER = 'cmsmart_mp/landing_page/seller_id';
	const ADMIN_COMMISSION_AMOUNT = 'cmsmart_mp/commission/amount';
	const ADMIN_COMMISSION_OPTION = 'cmsmart_mp/commission/fixed_or_percentage';
	const ADMIN_COMMISSION_TYPE = 'cmsmart_mp/commission/type';
	const LOCATOR_ENABLE = 'cmsmart_mp/locator/enable';
	const LOCATOR_DISTANCE_UNIT = 'cmsmart_mp/locator/distance_unit';
	const LOCATOR_GOOGLE_API_KEY = 'cmsmart_mp/locator/google_api';
	const VACATION_ENABLE = 'cmsmart_mp/vacation/enable';

	protected $_storeManager;

	protected $_template;

	/**
	 * @var \Magento\Framework\Translate\Inline\StateInterface
	 */
	protected $_inlineTranslation;

	/**
	 * @var \Magento\Framework\Mail\Template\TransportBuilder
	 */
	protected $_transportBuilder;

	protected $_objectManager;

	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\Locale\CurrencyInterface $localeCurrency
	) {
		$this->_storeManager = $storeManager;
		$this->_customerSession = $customerSession;
		$this->_objectManager = $objectManager;
		$this->_inlineTranslation = $inlineTranslation;
		$this->_transportBuilder = $transportBuilder;
		$this->_localeCurrency = $localeCurrency;
		parent::__construct($context);
	}

	public function storeImageExtension() {
		return $this->scopeConfig->getValue(self::UPLOAD_IMAGE_TYPE);
	}

	public function getAllowedAttributesetIds() {
		$current_store = $this->_storeManager->getStore();
		return $this->scopeConfig->getValue(self::ATTRIBUTE_SET_ID);
	}

	public function getCurrencySymbol() {
		return $this->_localeCurrency->getCurrency(
			$this->getBaseCurrencyCode()
		)->getSymbol();
	}

	public function getBaseCurrencyCode() {
		return $this->_storeManager->getStore()->getBaseCurrencyCode();
	}

	public function getAllowedProductTypes() {
		return $this->scopeConfig->getValue(self::ALLOWED_PRODUCT_TYPE);
	}

	public function getAllowedSkuTypes() {
		return $this->scopeConfig->getValue(self::ALLOWED_SKU_TYPE);
	}

	public function getSkuPrefix() {
		return $this->scopeConfig->getValue(self::ALLOWED_SKU_PREFIX);
	}

	public function getCommissionAmount() {
		return $this->scopeConfig->getValue(self::ADMIN_COMMISSION_AMOUNT);
	}

	public function getCommissionOption() {
		return $this->scopeConfig->getValue(self::ADMIN_COMMISSION_OPTION);
	}

	public function getCommissionType() {
		return $this->scopeConfig->getValue(self::ADMIN_COMMISSION_TYPE);
	}

	public function getLandingPage() {
		return $this->scopeConfig->getValue(self::ADMIN_LANDING_PAGE);
	}

	public function getHotsellers() {
		return $this->scopeConfig->getValue(self::ADMIN_LANDINGPAGE_HOTSELLER);
	}

	public function getSellerId() {
		$customerId = $this->_customerSession->getCustomerId();

		$model = $this->_objectManager->create('Cmsmart\Marketplace\Model\Seller');

		$seller = $model->getCollection()
			->addFieldToFilter('seller_id', $customerId)
			->addFieldToFilter('status', 1);
		if ($seller->getData()) {
			return $seller->getData()[0]['seller_id'];
		} else {
			return '';
		}
	}

	public function getIsProductEditApproval() {
		return $this->scopeConfig->getValue(self::PRODUCT_EDIT_APPROVAL);
	}

	public function getIsProductApproval() {
		return $this->scopeConfig->getValue(self::PRODUCT_APPROVAL);
	}

	public function getIsOrderApproval() {
		return $this->scopeConfig->getValue(self::ORDER_APPROVAL);
	}

	/**
	 * Return the authorize seller status.
	 *
	 * @return bool|0|1
	 */
	public function isCorrectSeller($productId) {
		$data = 0;
		if ($productId) {
			$model = $this->_objectManager->create(
				'Cmsmart\Marketplace\Model\Product'
			)
				->getCollection()
				->addFieldToFilter(
					'product_id',
					$productId
				)->addFieldToFilter(
				'seller_id',
				$this->_customerSession->getCustomerId()
			);

			if ($model->getData()) {
				$data = 1;
			}
		}

		return $data;
	}

	/**
	 * Retrieve YouTube API key
	 *
	 * @return string
	 */
	public function getYouTubeApiKey() {
		return $this->scopeConfig->getValue(
			'catalog/product_video/youtube_api_key'
		);
	}

	public function getWebsiteId() {
		return $this->_storeManager->getStore(true)->getWebsite()->getId();
	}

	public function getSingleStoreStatus() {
		return $this->_storeManager->hasSingleStore();
	}

	public function getCurrentStoreId() {
		return $this->_storeManager->getStore()->getStoreId();
	}

	public function getTermandCondition() {
		return $this->scopeConfig->getValue(self::TERM_AND_CONDITION);
	}

	public function getAdminEmailId() {
		return $this->scopeConfig->getValue(self::ADMIN_EMAIL_ID,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	public function getDefaultTransEmailId() {
		return $this->scopeConfig->getValue(
			'trans_email/ident_general/email',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 *
	 * @param Mixed $emailTemplateVariables
	 * @param Mixed $senderInfo
	 * @param Mixed $receiverInfo
	 */
	public function sendProductMail($emailTemplateVariables, $senderInfo, $receiverInfo) {
		$this->_template = $this->getTemplateId(self::PRODUCT_EMAIL_TEMPLATE);

		$this->_inlineTranslation->suspend();
		$this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
		$transport = $this->_transportBuilder->getTransport();
		$transport->sendMessage();
		$this->_inlineTranslation->resume();
	}

	public function sendAccountMail($emailTemplateVariables, $senderInfo, $receiverInfo) {
		$this->_template = $this->getTemplateId(self::ACCOUNT_EMAIL_TEMPLATE);

		$this->_inlineTranslation->suspend();
		$this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
		$transport = $this->_transportBuilder->getTransport();
		$transport->sendMessage();
		$this->_inlineTranslation->resume();
	}

	public function sendContactMail($emailTemplateVariables, $senderInfo, $receiverInfo) {
		$this->_template = $this->getTemplateId(self::CONTACT_EMAIL_TEMPLATE);

		$this->_inlineTranslation->suspend();
		$this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
		$transport = $this->_transportBuilder->getTransport();
		$transport->sendMessage();
		$this->_inlineTranslation->resume();
	}

	public function sendSellerMail($emailTemplateVariables, $senderInfo, $receiverInfo) {
		$this->_template = $this->getTemplateId(self::SELLER_EMAIL_TEMPLATE);

		$this->_inlineTranslation->suspend();
		$this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
		$transport = $this->_transportBuilder->getTransport();
		$transport->sendMessage();
		$this->_inlineTranslation->resume();
	}

	public function sendAdminProductMail($emailTemplateVariables, $senderInfo, $receiverInfo) {
		$this->_template = $this->getTemplateId(self::ADMIN_PRODUCT_EMAIL_TEMPLATE);

		$this->_inlineTranslation->suspend();
		$this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
		$transport = $this->_transportBuilder->getTransport();
		$transport->sendMessage();
		$this->_inlineTranslation->resume();
	}

	public function sendPaysellerMail($emailTemplateVariables, $senderInfo, $receiverInfo) {
		$this->_template = $this->getTemplateId(self::PAY_SELLER_EMAIL_TEMPLATE);

		$this->_inlineTranslation->suspend();
		$this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
		$transport = $this->_transportBuilder->getTransport();
		$transport->sendMessage();
		$this->_inlineTranslation->resume();
	}

	/**
	 * Return template id.
	 *
	 * @return mixed
	 */
	public function getTemplateId($xmlPath) {
		return $this->scopeConfig->getValue($xmlPath);
	}

	/**
	 * [generateTemplate description].
	 *
	 * @param Mixed $emailTemplateVariables
	 * @param Mixed $senderInfo
	 * @param Mixed $receiverInfo
	 */
	public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo) {
		$template = $this->_transportBuilder->setTemplateIdentifier($this->_template)
			->setTemplateOptions(
				[
					'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
					'store' => $this->_storeManager->getStore()->getId(),
				]
			)
			->setTemplateVars($emailTemplateVariables)
			->setFrom($senderInfo)
			->addTo($receiverInfo['email'], $receiverInfo['name']);

		return $this;
	}

	public function getMediaUrl() {
		return $this->_storeManager->getStore()->getBaseUrl(
			\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
		);
	}

	public function isLocatorEnable() {
		return $this->scopeConfig->getValue(self::LOCATOR_ENABLE);
	}

	public function getDistanceUnit() {
		return $this->scopeConfig->getValue(self::LOCATOR_DISTANCE_UNIT);
	}

	public function getGoogleApiKey() {
		if ($this->scopeConfig->getValue(self::LOCATOR_GOOGLE_API_KEY)) {
			return $this->scopeConfig->getValue(self::LOCATOR_GOOGLE_API_KEY);
		} else {
			return "AIzaSyA2R7NEXim1UxTR1O3wbVaI8ma9ad2ziFs";
		}
	}

	public function isVacation() {
		return $this->scopeConfig->getValue(self::VACATION_ENABLE);
	}

	public function top_get_categories() {
		$category = $this->_objectManager->create('Magento\Catalog\Model\Category');
		$tree = $category->getTreeModel();
		$tree->load();
		$ids = $tree->getCollection()->addFieldToFilter('is_active', array('eq' => '1'))->getAllIds();
		$arr = array();
		$arrayCategories = array();
		if ($ids) {
			foreach ($ids as $id) {
				$cat = $this->_objectManager->create('Magento\Catalog\Model\Category');
				$cat->load($id);

				$arrayCategories[$id] =
				array("parent_id" => $cat->getParentId(),
					"name" => $cat->getName(),
					"cat_id" => $cat->getId(),
					"cat_level" => $cat->getLevel(),
					"cat_url" => $cat->getUrl(),
				);

			} // for each ends
			return $arrayCategories;
		}
	}

	public function top_createTree($array, $currentParent, $currLevel, $prevLevel, $product) {
		$catalogSession = $this->_objectManager->create('Magento\Catalog\Model\Session');
		$selected_arr = explode(",", $catalogSession->getTopCatIds());
		$str = '';
		$i = 0;
		$arr = array();
		if (isset($product)) {
			$arr = $product->getData('category_ids');
		}
		foreach ($array as $categoryId => $category) {
			if ($currentParent == $category['parent_id']) {
				if ($currLevel > $prevLevel) {
					$str .= "<ul id='ul_" . $categoryId . "'>";
				}

				if ($currLevel == $prevLevel) {
					$str .= " </li> ";
				}

				$cat_id_hidden = array('1', '2');

				$chk = "";
				if (in_array($categoryId, $selected_arr)) {
					$chk = ' checked="true"';
				}

				$str .= '
                <li id="li_' . $categoryId . '">
                <img id="ext-cus-' . $categoryId . '" class="my_folder" alt="">';
				if ($categoryId > 1) {
					if (isset($product)) {
						$check = "";
						if (isset($arr[$i]) && in_array($categoryId, $arr)) {
							$check = "checked";

						}
						$str .= '<input type="checkbox" class="mytree-l-tcb" name="product[category_ids][]" id="ext-gen' . $categoryId . '" ' . $chk . ' value="' . $categoryId . '" ' . $check . '>';
					} else {
						$str .= '<input type="checkbox" class="mytree-l-tcb" name="product[category_ids][]" id="ext-gen' . $categoryId . '" ' . $chk . ' value="' . $categoryId . '">';
					}

				}
				$str .= '<span id="span_' . $categoryId . '">' . $category['name'] . '</span>';
				if ($currLevel > $prevLevel) {
					$prevLevel = $currLevel;
				}
				$currLevel++;
				$this->top_createTree($array, $categoryId, $currLevel, $prevLevel, $product);
				$currLevel--;
			}
		}
		if ($currLevel == $prevLevel) {
			$str .= " </li></ul> ";
		}
		return $str;
	}

}