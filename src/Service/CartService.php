<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const KEY = 'cart';

    public function __construct(
        private RequestStack $requestStack,
        private ProductRepository $products
    ) {}

    private function session() { return $this->requestStack->getSession(); }

    public function add(int $productId, int $qty = 1): void
    {
        $cart = $this->session()->get(self::KEY, []);
        $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
        $this->session()->set(self::KEY, $cart);
    }

    public function update(int $productId, int $qty): void
    {
        $cart = $this->session()->get(self::KEY, []);
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }
        $this->session()->set(self::KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->session()->get(self::KEY, []);
        unset($cart[$productId]);
        $this->session()->set(self::KEY, $cart);
    }

    public function clear(): void
    {
        $this->session()->remove(self::KEY);
    }

    public function getDetailed(): array
    {
        $cart = $this->session()->get(self::KEY, []);
        $items = [];
        $total = 0;
        foreach ($cart as $pid => $qty) {
            $p = $this->products->find($pid);
            if (!$p) continue;
            $line = (float)$p->getPrice() * $qty;
            $items[] = ['product' => $p, 'qty' => $qty, 'lineTotal' => $line];
            $total += $line;
        }
        return ['items' => $items, 'total' => $total];
    }

    public function count(): int
    {
        return array_sum($this->session()->get(self::KEY, []));
    }
}