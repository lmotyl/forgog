<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{

    const PRODUCT_PAGE_LIMIT = 3;

    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }


    /**
     * @Route("/products/{page}", defaults={"page"=1}, name="list_product", methods={"GET"})
     */
    public function index($page): JsonResponse
    {
        $offset = ($page - 1) >= 0 ? self::PRODUCT_PAGE_LIMIT*($page - 1) : 0;

        $products = $this->productRepository->list($offset, self::PRODUCT_PAGE_LIMIT);

        $data = [];

        foreach ($products as $product) {
            /** @var Product $product */
            $data[] = $product->toArray();
        }

        return $this->json([
                'products' => $data
            ]
        );
    }

    /**
     * @Route("/products/", name="add_product", methods={"POST"})
     */
    public function add(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $title = $data['title'] ?? null;
        $price = $data['price'] ?? null;

        $product = new Product();
        $product->setTitle($title);
        $product->setPrice($price);

        $errors = $validator->validate($product);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;

            return new JsonResponse(
                ['status' => 'Validation Failed', 'errors' => $errorsString],
                Response::HTTP_CONFLICT
            );
        }

        $this->productRepository->saveProduct($title, $price);

        return new JsonResponse(['status' => 'Product created!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/products/{id}", name="delete_product", methods={"DELETE"})
     */
    public function delete($id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        $this->productRepository->deleteProduct($product);

        return new JsonResponse(['status' => 'Product was deleted!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/products/{id}", name="update_product", methods={"PUT"})
     */
    public function update($id, Request $request, ValidatorInterface $validator): JsonResponse
    {
        $product = $this->productRepository->find($id);
        $data = json_decode($request->getContent(), true);

        $title = $data['title'] ?? null;
        $price = $data['price'] ?? null;

        if ($title) {
            $product->setTitle($title);
        }

        if ($price) {
            $product->setPrice($price);
        }

        $errors = $validator->validate($product);

        if (count($errors) > 0) {
            $errorsString = (string)$errors;

            return new JsonResponse(
                ['status' => 'Validation Failed', 'errors' => $errorsString],
                Response::HTTP_CONFLICT
            );
        }

        $this->productRepository->updateProduct($product);

        return new JsonResponse(['status' => 'Product updated!'], Response::HTTP_CREATED);
    }
}
