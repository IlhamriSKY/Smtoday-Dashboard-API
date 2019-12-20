<?php

namespace Vanguard\Http\Controllers\Api;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Response;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Vanguard\Http\Controllers\Controller;
use Vanguard\Support\DataArraySerializer;

abstract class ApiController extends Controller
{
    protected $statusCode = 200;

    protected $fractal;

    /**
     * @return Manager
     */
    protected function fractal()
    {
        if ($this->fractal) {
            return $this->fractal;
        }

        $fractal = app(Manager::class);
        $fractal->setRecursionLimit(2);
        $fractal->setSerializer(new DataArraySerializer);

        if ($includes = request('include')) {
            $fractal->parseIncludes($includes);
        }

        return $this->fractal = $fractal;
    }

    /**
     * Getter for statusCode
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    protected function respondWithItem($item, $callback)
    {
        if ($includes = $this->getValidIncludes($callback)) {
            $item->load($includes);
        }

        $resource = new Item($item, $callback);

        $rootScope = $this->fractal()->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    protected function respondWithCollection($collection, $callback)
    {
        if ($includes = $this->getValidIncludes($callback)) {
            $collection->load($includes);
        }

        $resource = new Collection($collection, $callback);

        $rootScope = $this->fractal()->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    protected function respondWithPagination(Paginator $paginator, $callback)
    {
        if ($includes = $this->getValidIncludes($callback)) {
            $paginator->load($includes);
        }

        $queryParams = array_diff_key($_GET, array_flip(['page']));
        $paginator->appends($queryParams);

        $resource = new Collection($paginator, $callback, 'data');
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        $rootScope = $this->fractal()->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    private function getValidIncludes($callback)
    {
        $includes = $this->fractal()->getRequestedIncludes();

        if (! $includes) {
            return null;
        }

        return array_intersect(
            $includes,
            $callback->getAvailableIncludes()
        );
    }

    protected function respondWithSuccess($statusCode = 200)
    {
        return $this->setStatusCode($statusCode)
            ->respondWithArray(['success' => true]);
    }

    protected function respondWithArray(array $array, array $headers = [])
    {
        $response = \Response::json($array, $this->statusCode, $headers);

        $response->header('Content-Type', 'application/json');

        return $response;
    }

    protected function respondWithError($message)
    {
        if ($this->statusCode === 200) {
            trigger_error(
                "You better have a really good reason for erroring on a 200...",
                E_USER_WARNING
            );
        }

        return $this->respondWithArray([
            'error' => $message
        ]);
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)
            ->respondWithError($message);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)
            ->respondWithError($message);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)
            ->respondWithError($message);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)
            ->respondWithError($message);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @param string $message
     * @return Response
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)
            ->respondWithError($message);
    }
}
