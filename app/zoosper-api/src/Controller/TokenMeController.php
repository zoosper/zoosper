<?php

declare(strict_types=1);
namespace Zoosper\Api\Controller;
use Zoosper\Auth\Token\BearerPrincipalContext;use Zoosper\Core\Http\JsonResponder;use Zoosper\Core\Http\Request;use Zoosper\Core\Http\Response;
/**
 * Declarative runtime entry point discovered through module configuration.
 *
 * @psalm-api
 */
final readonly class TokenMeController
{
    public function __construct(private JsonResponder $json,private BearerPrincipalContext $principals){}
    public function show(Request $request):Response{$p=$this->principals->require($request);return $this->json->success(['authentication'=>['type'=>'personal_access_token','token_public_id'=>$p->token->publicId,'token_name'=>$p->token->name,'scopes'=>$p->token->scopes],'user'=>['id'=>$p->user->id,'email'=>$p->user->email,'name'=>$p->user->name,'permissions'=>$p->user->permissions]]);}
}










