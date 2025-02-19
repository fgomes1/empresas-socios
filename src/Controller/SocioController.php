<?php

namespace App\Controller;

use App\Entity\Socio;
use App\Entity\Empresa;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;

#[Route('/api/empresas/{empresaId}/socios')]
class SocioController extends AbstractController
{
    #[Route('', name: 'socio_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Listar os sócios de uma empresa',
        description: 'Retorna uma lista dos sócios associados a uma empresa específica.',
        parameters: [
            new OA\Parameter(
                name: 'empresaId',
                in: 'path',
                description: 'ID da empresa',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de sócios retornada com sucesso',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Socio')
                )
            ),
            new OA\Response(response: 404, description: 'Empresa não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno')
        ]
    )]
    public function index(int $empresaId, Empresa $empresa): JsonResponse
    {
        $socios = $empresa->getSocios();
        return $this->json($socios);
    }

    #[Route('', name: 'socio_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Criar um novo sócio',
        description: 'Cria um novo sócio e o vincula à empresa especificada.',
        parameters: [
            new OA\Parameter(
                name: 'empresaId',
                in: 'path',
                description: 'ID da empresa',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nome'],
                properties: [
                    new OA\Property(property: 'nome', type: 'string', example: 'Sócio Exemplo', description: 'Nome do sócio')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Sócio criado com sucesso', content: new OA\JsonContent(ref: '#/components/schemas/Socio')),
            new OA\Response(response: 400, description: 'Dados inválidos'),
            new OA\Response(response: 404, description: 'Empresa não encontrada'),
            new OA\Response(response: 500, description: 'Erro interno')
        ]
    )]
    public function create(int $empresaId, Request $request, EntityManagerInterface $em, Empresa $empresa): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nome'])) {
            return new JsonResponse(['error' => 'O campo "nome" é obrigatório'], Response::HTTP_BAD_REQUEST);
        }

        $socio = new Socio();
        $socio->setNome($data['nome']);
        $socio->setEmpresa($empresa);

        $em->persist($socio);
        $em->flush();

        return new JsonResponse($socio, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'socio_show', methods: ['GET'])]
    #[OA\Get(
        summary: 'Exibir detalhes de um sócio',
        description: 'Retorna os detalhes de um sócio específico associado a uma empresa.',
        parameters: [
            new OA\Parameter(
                name: 'empresaId',
                in: 'path',
                description: 'ID da empresa',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID do sócio',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalhes do sócio', content: new OA\JsonContent(ref: '#/components/schemas/Socio')),
            new OA\Response(response: 404, description: 'Sócio não encontrado')
        ]
    )]
    public function show(int $empresaId, Socio $socio): JsonResponse
    {
        return $this->json($socio);
    }

    #[Route('/{id}', name: 'socio_update', methods: ['PUT'])]
    #[OA\Put(
        summary: 'Atualizar um sócio',
        description: 'Atualiza os dados de um sócio associado a uma empresa.',
        parameters: [
            new OA\Parameter(
                name: 'empresaId',
                in: 'path',
                description: 'ID da empresa',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID do sócio',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'nome', type: 'string', example: 'Sócio Atualizado', description: 'Nome atualizado do sócio')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Sócio atualizado com sucesso', content: new OA\JsonContent(ref: '#/components/schemas/Socio')),
            new OA\Response(response: 400, description: 'Dados inválidos'),
            new OA\Response(response: 404, description: 'Sócio não encontrado')
        ]
    )]
    public function update(int $empresaId, Request $request, Socio $socio, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['nome'])) {
            $socio->setNome($data['nome']);
        }

        $em->flush();

        return new JsonResponse($socio);
    }

    #[Route('/{id}', name: 'socio_delete', methods: ['DELETE'])]
    #[OA\Delete(
        summary: 'Remover um sócio',
        description: 'Remove um sócio associado a uma empresa.',
        parameters: [
            new OA\Parameter(
                name: 'empresaId',
                in: 'path',
                description: 'ID da empresa',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'ID do sócio',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Sócio removido com sucesso'),
            new OA\Response(response: 404, description: 'Sócio não encontrado')
        ]
    )]
    public function delete(Socio $socio, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($socio);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
