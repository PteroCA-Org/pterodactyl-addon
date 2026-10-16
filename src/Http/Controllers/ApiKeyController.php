<?php

namespace Pteroca\PterodactylAddon\Http\Controllers;

use Pterodactyl\Http\Controllers\Api\Application\ApplicationApiController;
use Pteroca\PterodactylAddon\Http\Requests\GetUsersApiKeysRequest;
use Pteroca\PterodactylAddon\Http\Requests\StoreUserApiKeyRequest;
use Pteroca\PterodactylAddon\Http\Requests\DeleteUserApiKeyRequest;
use Pterodactyl\Models\ApiKey;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\User;
use Pterodactyl\Transformers\Api\Client\ApiKeyTransformer;

class ApiKeyController extends ApplicationApiController
{
    /**
     * Returns all of the API keys that exist for the given client.
     *
     * @return array
     */
    public function index(GetUsersApiKeysRequest $request, $user)
    {
        $user = User::findOrFail($user);
        return $this->fractal->collection($user->apiKeys)
            ->transformWith($this->getTransformer(ApiKeyTransformer::class))
            ->toArray();
    }

    /**
     * Store a new API key for a user's account.
     *
     * @return array
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     */
    public function store(StoreUserApiKeyRequest $request, $user)
    {
        $user = User::findOrFail($user);

        $token = $user->createToken(
            $request->input('description'),
            $request->input('allowed_ips', [])
        );

        return $this->fractal->item($token->accessToken)
            ->transformWith($this->getTransformer(ApiKeyTransformer::class))
            ->addMeta([
                'secret_token' => $token->plainTextToken
            ])
            ->toArray();
    }

    /**
     * Deletes a given API key.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteUserApiKeyRequest $request, $user, string $identifier)
    {
        $user = User::findOrFail($user);

        $key = $user->apiKeys()
            ->where('key_type', ApiKey::TYPE_ACCOUNT)
            ->where('identifier', $identifier)
            ->firstOrFail();

        $key->delete();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
