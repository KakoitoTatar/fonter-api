<?php

declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Application\Helpers\StringHelper;
use App\Application\Settings\SettingsInterface;
use App\Application\Validator\ValidatorInterface;
use App\Domain\Mail\Mail;
use App\Domain\User\User;
use App\Domain\User\UserRepository as UserRepositoryInterface;
use App\Domain\Mail\MailRepositoryInterface;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * @OA\Post(
 *     path="/api/users",
 *     tags={"Users"},
 *     @OA\Parameter(
 *         name="email",
 *         in="query",
 *         required=true,
 *         @OA\Schema(
 *             type="string"
 *         )
 *      ),
 *      @OA\Parameter(
 *          name="password",
 *          in="query",
 *          required=true,
 *          @OA\Schema(
 *             type="string",
 *             maxLength=64,
 *             minLength=8
 *         )
 *      ),
 *      @OA\Response(
 *          response="200",
 *          description="an OK response",
 *          @OA\JsonContent(ref="#/components/schemas/Registration201Response")
 *      ),
 *      @OA\Response(
 *          response="400",
 *          description="Validation errors response",
 *          @OA\JsonContent(ref="#/components/schemas/400Response")
 *      ),
 *      @OA\Response(
 *          response="403",
 *          description="Wrong access rights exception",
 *          @OA\JsonContent(ref="#/components/schemas/403Response")
 *      )
 * )
 * @OA\Schema(
 *     schema="Registration201Response",
 *     @OA\Property(property="message", type="string", example="Регистрация прошла успешно, проверьте почту туда было выслано сообщение для подтверждения")
 * )
 */
class PostUser extends UserAction
{
    /**
     * @var MailRepositoryInterface
     */
    protected MailRepositoryInterface $mailRepository;

    /**
     * @var SettingsInterface
     */
    protected SettingsInterface $settings;

    /**
     * PostUser constructor.
     * @param LoggerInterface $logger
     * @param UserRepositoryInterface $userRepository
     * @param MailRepositoryInterface $mailRepository
     * @param SettingsInterface $settings
     * @param Serializer $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(
        LoggerInterface $logger,
        UserRepositoryInterface $userRepository,
        MailRepositoryInterface $mailRepository,
        SettingsInterface $settings,
        Serializer $serializer,
        ValidatorInterface $validator
    ) {
        parent::__construct($logger, $userRepository, $serializer, $validator);
        $this->mailRepository = $mailRepository;
        $this->settings = $settings;
    }

    /**
     * @return Response
     * @throws Exception
     */
    protected function action(): Response
    {
        $data = $this->request->getParsedBody();

        $user = new User();
        $user->fillNewUser(
            $data['email'],
            User::INACTIVE_USER,
            StringHelper::generateRandomString(64),
            $data['password']
        );

        $this->userRepository->save($user);

        $confirmationMail = new Mail(
            $this->settings->get('email')['no-reply'],
            $data['email'],
            'Подтверждение регистрации на The Friday Drop',
            'registration-confirmation',
            [
                'LINK' => $this->settings->get('baseUrl') . '/users/' . $user->getId() . '/activate/' . $user->getSecretToken(),
                'EMAIL' => $user->getEmail()
            ]
        );

        $this->mailRepository->save($confirmationMail);

        return $this->respondWithData(
            [
                'message' => 'Регистрация прошла успешно, проверьте почту туда было выслано сообщение для подтверждения'
            ],
            201
        );
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::GUEST
        ];
    }

    /**
     * @return string[]
     */
    protected function getRules(): array
    {
        return [
            'email' => ['required', 'email' ,['unique', User::class, 'email']],
            'password' => ['required',['lengthBetween', 8, 64]],
        ];
    }
}
