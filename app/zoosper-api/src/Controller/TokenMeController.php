<?php

declare(strict_types=1);
namespace Zoosper\Api\Controller;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;use Zoosper\Core\Http\JsonResponder;use Zoosper\Core\Http\Request;use Zoosper\Core\Http\Response;
final readonly class TokenMeController
{
    public function __construct(private JsonResponder $json,private PersonalAccessTokenAuthenticator $auth){}
    public function show(Request $request):Response{$p=$this->auth->authenticate($request->header('authorization'));if($p===null)return $this->json->error('invalid_bearer_token','A valid bearer token is required.',401);return $this->json->success(['authentication'=>['type'=>'personal_access_token','token_public_id'=>$p->token->publicId,'token_name'=>$p->token->name,'scopes'=>$p->token->scopes],'user'=>['id'=>$p->user->id,'email'=>$p->user->email,'name'=>$p->user->name,'permissions'=>$p->user->permissions]]);}
}
