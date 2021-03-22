<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use App\Repository\CartProductRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Phalcon\Assets\Inline\Js;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CartController extends AbstractController
{
    /**
     * @var CartRepository
     */
    private $cartRepository;

    /**
     * @var CartProductRepository
     */
    private $cartProductRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(
        CartRepository $cartRepository,
        ProductRepository $productRepository,
        CartProductRepository $cartProductsRepository
    )
    {
        $this->cartRepository = $cartRepository;
        $this->cartProductRepository = $cartProductsRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/cart/", name="add_cart", methods={"POST"})
     */

    public function add(): JsonResponse
    {
        $cart = new Cart();

        $this->cartRepository->save($cart);

        return new JsonResponse(['status' => 'Cart created!',  'cart' => $cart->toArray()], Response::HTTP_CREATED);
    }

    /**
     * @Route("/cart/{cartId}", name="add_cart", methods={"GET"})
     */

    public function get($cartId): JsonResponse
    {
        $cart = $this->cartRepository->find($cartId);

        if (is_null($cart)) {
            return new JsonResponse(['status' => 'Cart not found!'], Response::HTTP_NOT_FOUND);
        }

        $cartData = $cart->toArray();
        $cartProducts = $cart->getCartProducts();

        $sum = 0;
        foreach ($cartProducts as $cartProduct) {
            $product = $cartProduct->getProduct()->toArray();
            $product['quantity'] = $cartProduct->getQuantity();
            $sum += $cartProduct->getProduct()->getPrice()*$cartProduct->getQuantity();
            $cartData['products'][] = $product;
        }

        $cartData['sum'] = number_format($sum/100, 2);

        return new JsonResponse(['status' => 'Cart created!',  'cart' => $cartData], Response::HTTP_CREATED);
    }

    /**
     * @Route("/cart/product/", name="add_cart_product", methods={"POST"})
     */
    public function addProduct(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $cartId = $data['cart_id'] ?? null;
        $productId = $data['product_id'] ?? null;
        $quantity = $data['quantity'] ?? 1;

        $cart = $this->cartRepository->find($cartId);
        $product = $this->productRepository->find($productId);

        if (is_null($cart) || is_null($product)) {
            return new JsonResponse(
                ['status' => 'Product or Cart does not exists!'],
                Response::HTTP_BAD_REQUEST
            );

        }

        $cartProduct = $this->cartProductRepository->findOneBy([
            'cart' => $cart,
            'product' => $product
        ]);

        if (is_null($cartProduct)) {
            $cartProduct = new CartProduct();
            $cartProduct->setProduct($product);
            $cart->addCartProduct($cartProduct);

        }
        $cartProduct->setQuantity($cartProduct->getQuantity()+$quantity);

        $errors = $validator->validate($cartProduct);
        $errorsCart = $validator->validate($cart);

        if (count($errorsCart) > 0) {
            $errorsString = (string)$errorsCart;

            return new JsonResponse(
                ['status' => 'Validation Failed', 'errors' => $errorsString],
                Response::HTTP_CONFLICT
            );
        }

        if (count($errors) > 0) {
            $errorsString = (string)$errors;

            return new JsonResponse(
                ['status' => 'Validation Failed', 'errors' => $errorsString],
                Response::HTTP_CONFLICT
            );
        }

        $this->cartRepository->save($cart);

        return new JsonResponse(['status' => 'Product has been added!'], Response::HTTP_CREATED);
    }


    /**
     * @Route("/cart/product/", name="remove_cart_product", methods={"DELETE"})
     */
    public function removeProduct(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $cartId = $data['cart_id'] ?? null;
        $productId = $data['product_id'] ?? null;

        $cart = $this->cartRepository->find($cartId);
        $product = $this->productRepository->find($productId);

        if (is_null($cart) || is_null($product)) {
            return new JsonResponse(
                ['status' => 'Product or Cart does not exists!'],
                Response::HTTP_BAD_REQUEST
            );

        }

        $cartProduct = $this->cartProductRepository->findOneBy([
            'cart' => $cart,
            'product' => $product
        ]);

        $this->cartProductRepository->remove($cartProduct);

        return new JsonResponse(['status' => 'Product has been deleted!'], Response::HTTP_CREATED);

    }


}
