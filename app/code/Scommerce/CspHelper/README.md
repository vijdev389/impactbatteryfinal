# CSP Helper Extension

This Magento 2 extension helps you resolve Content Security Policy (CSP) issues caused by inline JavaScript code in your store. It achieves this by automatically adding a nonce attribute to all your inline script tags.

## Features
•  Automatic nonce generation for inline scripts.

•  Dependency injection for easy integration into your custom code.

•  Improved code maintainability by separating script logic from security concerns.


## Installation
1. Add the repository:

```bash
composer require scommerce/csp-helper
```

2. In your code, wherever a **<script>** tag is used, incorporate the helper class and append the **getNonce** function as shown below
```bash
<?php $cspHelper = $this->helper('\Scommerce\CspHelper\Helper\CspHelper'); ?>
<script type="text/javascript" <?= $cspHelper->generateNonce(); ?>>
       // Your script code here
</script>
```

**Important Note**

If the **generateNonce()** function fails to generate a nonce (potentially on Magento versions 2.4.6 and below), an empty string will be added to the nonce attribute.

**How it Works**

The extension utilises the **Scommerce\CspHelper\Helper\CspHelper** class. This class injects itself into your Helper, Block, or ViewModel classes using dependency injection. The **getNonce()** function within this class generates a unique, random string called a nonce. This nonce is then added as the nonce attribute to your inline script tags.

**Benefits**

•  Simplifies CSP compliance.

•  Reduces the risk of malicious script execution.

•  Improves code maintainability.

**Need Help**

If you require assistance with implementing this on your website to resolve CSP inline JavaScript errors, feel free to reach out to us via email at [support@scommerce-mage.com](mailto:support@scommerce-mage.com).

**Looking for a Complete Solution?**

For a comprehensive resolution of other CSP errors on your site, consider utilising our <a href="https://www.scommerce-mage.com/magento-2-csp-whitelisting.html" target="_blank">CSP Whitelist Extension</a>. It provides the capability to whitelist URLs for any CSP directive directly from the Magento admin panel. Learn more about it <a href="https://www.scommerce-mage.com/magento-2-csp-whitelisting.html" target="_blank">here</a>.


**Disclaimer**

This extension is provided as-is with no warranty. It is recommended to thoroughly test the extension in a development environment before deploying it to a live store.
