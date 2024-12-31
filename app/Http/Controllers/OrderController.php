<?php

namespace App\Http\Controllers;

use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    public function create(OrderCreateRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $response = $this->orderService->createOrderForClient($validatedData);

            return response()->json($response, Response::HTTP_CREATED);
        } catch (BadRequestException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        } catch (NotFoundException $exception) {
            return response()->json($exception->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            Log::error(
                "Error while creating order for client",
                [
                    'payload' => $validatedData,
                    'exception' => $exception->getMessage(),
                ]
            );
        }
    }
}
