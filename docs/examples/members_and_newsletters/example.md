Members & Newsletters
=====================

Say your `Member`'s model is configured to be managed by this bundle. If you want to use corresponding insert tags (like `##firstname##`) in any newsletters, they won't work well, as the whole newsletter system doesn't use the `Member`'s model but rather raw SQL queries (and thus will work on encrypted data).

To handle this use case :
- Contao < 5.0 : you will need to override the Newsletter sending system
- Contao > 5.0 : you will need to create an `EventListener` on the `SendNewsletterEvent`

## For Contao < 5.0

### Config

```php
// app/Resources/contao/config/config.php
if (isset($bundles['ContaoNewsletterBundle'])) {
	$GLOBALS['BE_MOD']['content']['newsletter']['send'] = [\App\Override\Newsletter::class,'send'];
}
```

### Override Newsletter sending

```php
<?php

namespace App\Override;

use Contao\Newsletter as ContaoNewsletter;
use Contao\Email;
use Contao\Database\Result;
use App\Model\Member; // Adapt to your needs

class Newsletter extends ContaoNewsletter {
    /**
     * Compile the newsletter and send it.
     *
     * @param array  $arrRecipient
     * @param string $text
     * @param string $html
     * @param string $css
     *
     * @return bool
     */
    protected function sendNewsletter(Email $objEmail, Result $objNewsletter, $arrRecipient, $text, $html, $css = null)
    {
        if (\array_key_exists('id', $arrRecipient)) {
            $objMember = Member::findByPk($arrRecipient['id']);
        } elseif (\array_key_exists('email', $arrRecipient)) {
            $objMember = Member::findByEmail($arrRecipient['email']);
        }
        if ($objMember) {
            $arrRecipient = array_merge($arrRecipient, $objMember->row());
        }

        return parent::sendNewsletter($objEmail, $objNewsletter, $arrRecipient, $text, $html, $css);
    }
}
```

## For Contao > 5.0

```php
// src/EventListener/SendNewsletterListener.php
namespace App\EventListener; // Adapt to your needs

use Contao\NewsletterBundle\Event\SendNewsletterEvent;
use Terminal42\ServiceAnnotationBundle\Annotation\ServiceTag;
use App\Model\Member; // Adapt to your needs

/**
 * @ServiceTag("kernel.event_listener")
 */
class SendNewsletterListener
{
    public function __invoke(SendNewsletterEvent $event): void
    {
    	$simpleTokenParser = System::getContainer()->get('contao.string.simple_token_parser');

       	$arrRecipient = $event->getRecipientData();

       	if (\array_key_exists('id', $arrRecipient)) {
            $objMember = Member::findByPk($arrRecipient['id']);
        } elseif (\array_key_exists('email', $arrRecipient)) {
            $objMember = Member::findByEmail($arrRecipient['email']);
        }
        if ($objMember) {
            $arrRecipient = array_merge($arrRecipient, $objMember->row());
        }

        $event->setRecipientData($arrRecipient);

        $event->setText($simpleTokenParser->parse($event->getNewsletterValue('text'), $arrRecipient));

        if($event->isHtmlAllowed()){
        	$objTemplate = new BackendTemplate($event->getNewsletterValue('template') ?: 'mail_default');
			$objTemplate->setData($event->getNewsletterData());
			$objTemplate->title = $event->getNewsletterValue('subject');
			$objTemplate->body = $simpleTokenParser->parse($html, $arrRecipient);
			$objTemplate->charset = System::getContainer()->getParameter('kernel.charset');
			$objTemplate->recipient = $arrRecipient['email'];
			$event->setHtml($objTemplate->parse());
        }
    }
}
```