<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use Closure;
use App\Models\Conta;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\SelectFields;
use Rebing\GraphQL\Support\Facades\GraphQL;
use GraphQL\Error\Error;

class SaqueMutation extends Mutation
{
    protected $attributes = [
        'name' => 'sacar',
        'description' => 'Realiza um saque de um valor de uma conta especificada.'
    ];

    public function type(): Type
    {
        return GraphQL::type('conta');
    }

    public function args(): array
    {
        return [
            'conta' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['min:5', 'max:5']
            ],
            'valor' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['integer','min:0']
            ]
        ];
    }

    public function validationErrorMessages(array $args = []): array
    {
        return [
            'valor.min' => 'Valor inválido',
            'valor.integer' => 'Valor inválido'
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        $conta = Conta::where('conta', $args['conta'])->first();
        if ($conta) {
            if ($conta->saldo >= $args['valor']) {
                $conta->saldo -= $args['valor'];
            } else {
                return new Error('Saldo insuficiente');
            }
            $conta->fill($args);
            $conta->save();
    
            return $conta;
        } else {
            return new Error('Conta não encontrada');
        }

    }
}
