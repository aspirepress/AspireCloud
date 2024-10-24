<?php

namespace App\Controller\API\WpOrg;

use App\Controller\BaseController;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use function Safe\json_encode;

class PassThroughController extends BaseController
{

    public function __construct(private HttpClientInterface $client, private EntityManagerInterface $em) {}

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    #[Route('/{proxy}', name: 'api_passthrough', requirements: ['proxy' => '.+'], priority: -100)]
    public function __invoke(Request $request): Response
    {
        $ua = $request->headers->get('User-Agent');
        $path = $request->getPathInfo();
        $queryParams = $request->query->all();

        $response = $this->client->request('GET', "https://api.wordpress.org/$path", [
            'headers' => ['User-Agent' => $ua, 'Accept' => '*/*'],
            'query' => $queryParams,
        ]);

        // Get content type and status code
        $contentType = $response->getHeaders()['Content-Type'] ?? null;
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();

        // Log request and response in DB
        $this->saveData($request, $response, $content);

        // Forward response through
        return new Response($content, $statusCode, ['Content-Type' => $contentType]);
    }

    private function saveData(Request $request, ResponseInterface $response, string $content): void
    {
        try {
            $this->em->getConnection()->insert('request_data', [
                'id' => Uuid::v7()->toString(),
                'request_path' => $request->getPathInfo(),
                'request_query_params' => json_encode($request->query->all()),
                'request_body' => json_encode($request->request->all()),
                'request_headers' => json_encode($request->headers->all()),
                'response_code' => $response->getStatusCode(),
                'response_body' => $content,
                'response_headers' => json_encode($response->getHeaders()),
                'created_at' => (new \DateTime())->format(DATE_ATOM),
            ]);
        } catch (\Throwable $e) {
            // TODO: log this properly
        }

    }
}
