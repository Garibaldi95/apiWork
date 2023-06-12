<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;


class SumHandler implements RequestHandlerInterface
{
    /**
     * Вывод суммы передаваемых параметров и запись их в логгер
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = date('Y-m-d');
        $log = new Logger('requests.log');
        $log->pushHandler(new StreamHandler('logs/' . $data));
        $log->pushHandler(new FirePHPHandler());
        $arr = $request->getQueryParams();
        if (count($arr) == 0) {
            $arr_sum = 0;
        } else {
            $arr_sum = array_sum($arr);
        }
        $log->info($arr_sum);
        return new JsonResponse([
            $arr_sum
        ]);
    }
}
