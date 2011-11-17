<?php    
namespace Zpi\UserBundle\Mailer;

use Symfony\Component\Templating\EngineInterface;
use \Swift_Mailer as Mailer;
use \Swift_Message as Message;
    
/**
 * Usługa służąca do rozsyłania konfigurowalnych powiadomień e-mail.
 * TODO Wysyłanie maili w danym czasie (podana data).
 * @author lyzkov
 */
class MessageManager
{

    protected $mailer;
    
    protected $templating;
    
    public function __construct(Mailer $mailer, EngineInterface $templating)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
    }
    
    /**
     * Wysyłanie maila do jednego adresata lub do wielu (ale wtedy wiedzą o sobie).
     * @param string $subject
     * @param string $from
     * @param string $to
     * @param string $twig
     * @param array $parameters
     * @param array $wildcards
     * @throws Exception
     * @author lyzkov
     */
    public function sendMail($subject, $from, $to, $twig, array $parameters = array())
    {
        $body = $this->templating->render($twig, $parameters);
        
        $message = Message::newInstance()
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body);
        $this->mailer->send($message);
    }
    
    /**
     * Wysyłanie maili do wielu adresatów.
     * TODO Trzeba będzie zrobić coś z tą pętlą, bo przy wielu adresatach się zajebie.
     * @param string $subject
     * @param string $from
     * @param array $to
     * @param unknown_type $twig
     * @param array $parameters
     * @param array $wildcards
     * @throws Exception
     * @author lyzkov
     */
    public function sendMails($subject, $from, array $to, $twig, array $parameters = array(), array $wildcards = array())
    {
//         if (!empty($wildcards))
//         {
            $body = $this->templating->render($twig, $parameters);
            foreach ($to as $k => $t)
            {
                if (!is_integer($k))
                {
                    throw new Exception("Error: Wrong \$to key: $k. Expecting: integer.");
                }
                if (!is_string($t))
                {
                    throw new Exception("Error: Wrong parameter \$to value type for key: $key. Expecting: string.");
                }
                $s = $subject;
                $b = $body;
                foreach ($wildcards as $key => $wildcard)
                {
                    if (!is_string($key))
                    {
                        throw new Exception("Error: Wrong \$wildcards key: $key. Expecting: string.");
                    }
                    if (!is_array($wildcard))
                    {
                        throw new Exception("Error: Wrong parameter \$wildcards value type for key: $key. Expecting: array.");
                    }
                    if (count($wildcard) != count($to))
                    {
                        throw new Exception("Error: Wrong parameter \$wildcards value type for key: $key. Number of values should be the same as \$to");
                    }
                    if (!is_string($wildcard[$k]))
                    {
                        throw new Exception("Error: Wrong parameter \$wildcards value type for key: $key. Expecting: array of strings.");
                    }
            
                    str_replace('%'.$key.'%', $wildcard[$k], $s);
                    str_replace('%'.$key.'%', $wildcard[$k], $b);
                }
            
                $message = Message::newInstance()
                    ->setSubject($s)
                    ->setFrom($from)
                    ->setTo($t)
                    ->setBody($b);
                $this->mailer->send($message);
            }
//         }
//         else
//         {
//             $body = $this->templating->render($twig, $parameters);
//             $message = Message::newInstance()
//                 ->setSubject($subject)
//                 ->setBody($body)
//                 ->setTo('undisclosed-recipients:;');
//             $recipients = new \Swift_RecipientList();
//             foreach ($to as $t)
//             {
//                 if (!is_string($t))
//                 {
//                     throw new Exception("Error: Wrong parameter \$to value type for key: $key. Expecting: string.");
//                 }
//                 $recipients->addTo($t);
//             }
//             $this->mailer->send($message, $recipients, $from);
//         }
    }
    
}