<?php

declare(strict_types=1);

namespace App\Application\Actions\Media;

use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Domain\Media\Media;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;

class DeleteMedia extends MediaAction
{
    /**
     * @return Response
     * @throws DomainRecordNotFoundException
     */
    protected function action(): Response
    {
        $data = $this->request->getParsedBody();

        /**
         * @var Media $media
         */
        $media = $this->repository->find($data['id']);

        if ($media === null) {
            throw new DomainRecordNotFoundException('Файл с таким id не существует.', 404);
        }

        $this->fileService->delete($media->getUrl());

        $this->repository->delete((int)$data['id']);

        return $this->respondWithData('Файл успешно удалён',200);
    }

    /**
     * @return array
     */
    protected function getAcceptedRoles(): array
    {
        return [
            User::ADMIN
        ];
    }

    /**
     * @return string[]
     */
    protected function getRules(): array
    {
        return [
            'id' => ['required', 'numeric']
        ];
    }
}