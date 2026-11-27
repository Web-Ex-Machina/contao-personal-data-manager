Members & Newsletters
=====================

Say your `Member`'s model is configured to be managed by this bundle. If you want to use corresponding insert tags (like `##firstname##`) in any newsletters, they won't work well, as the whole newsletter system doesn't use the `Member`'s model but rather raw SQL queries (and thus will work on encrypted data).

To handle this use case, you will need to create an `EventListener` on the `SendNewsletterEvent`.

```php
// src/EventListener/SendNewsletterListener.php
namespace App\EventListener; // Adapt to your needs

use Contao\NewsletterBundle\Event\SendNewsletterEvent;
use Contao\System;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Model\Member; // Adapt to your needs

#[AsEventListener]
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