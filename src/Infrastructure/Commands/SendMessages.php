<?php
declare(strict_types=1);

namespace App\Infrastructure\Commands;

use App\Application\Services\MailTemplateServiceInterface;
use App\Domain\Mail\Mail;
use App\Domain\Mail\MailRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Swift_Mailer;
use Swift_Message;

class SendMessages extends Command
{
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * @var MailRepositoryInterface
     */
    protected MailRepositoryInterface $mailRepository;

    /**
     * @var Swift_Mailer
     */
    protected Swift_Mailer $mailer;

    /**
     * @var string
     */
    protected static $defaultName = 'app:mails:send';

    /**
     * @var MailRepositoryInterface
     */
    protected MailRepositoryInterface $service;

    /**
     * @var array
     */
    protected array $settings;

    /**
     * SendMessages constructor.
     * @param Swift_Mailer $mailer
     * @param MailRepositoryInterface $mailRepository
     * @param MailTemplateServiceInterface $service
     * @param string|null $name
     */
    public function __construct(
        Swift_Mailer $mailer,
        MailRepositoryInterface $mailRepository,
        MailTemplateServiceInterface $service,
        string $name = null
    ) {
        $this->mailer = $mailer;
        $this->mailRepository = $mailRepository;
        $this->service = $service;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Рассылает все не отравленные сообщения');
        $this->setHelp('Рассылает все не отравленные сообщения');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->mailRepository;

        $mails = $repository->getAllNewNotSendedMails();

        /** @var Mail $mail */
        foreach ($mails as $mail) {
            $message = (new Swift_Message())
            ->setFrom([$mail->getAuthor() => 'The Friday Drop'])
            ->setTo([$mail->getReceiver()])
            ->setSubject($mail->getSubject());

            $mailBody = $this->service->makeBody($mail->getTemplate(), $mail->getData());

            $message->setBody($mailBody);

            $recipients = $this->mailer->send($message);

            if ($recipients === 0) {
                $mail->setStatus(Mail::ERROR);
            } else {
                $mail->setSendedAt(new \DateTime());
                $mail->setStatus(Mail::SUCCESS);
            }
        }

        $this->mailRepository->save($mail);

        return Command::SUCCESS;
    }
}
