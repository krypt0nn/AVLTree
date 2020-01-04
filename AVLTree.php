<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * @package     AVLTree
 * @copyright   2019 - 2020 Podvirnyy Nikita (Observer KRypt0n_)
 * @license     GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @author      Podvirnyy Nikita (KRypt0n_)
 * 
 * Contacts:
 *
 * Email: <suimin.tu.mu.ga.mi@gmail.com>
 * VK:    vk.com/technomindlp
 *        vk.com/hphp_convertation
 * 
 * 
 * @example
 * 
 * <?php
 * 
 * require 'AVLTree.php';
 * 
 * $tree = (new Tree (['a', 'c', 'e']))
 *    ->union (new Tree (['b', 'd']))
 *    ->massPush (['f', 'g'])
 *    ->push ('h');
 * 
 * $tree = $tree
 *     ->where (function ($node)
 *     {
 *         return ord ($node->getData ()) % 2 == 0;
 *     })
 *     ->getHips ();
 * 
 * print_r ($tree); // массив; выведет список элементов нового дерева ('b', 'd', 'f' и 'h')
 */

namespace AVLTree;

/**
 * Нода АВЛ-дерева
 */
class Node
{
    protected $data;

    public $height = 1;
    public $left   = null; // left < data
    public $right  = null; // right > data

    public function __construct (string $data)
    {
        $this->data = $data;
    }

    public function getData (): string
    {
        return $this->data;
    }

    /**
     * Получение фактора балансировки
     * 
     * @return int
     */
    protected function getBalanceFactor (): int
    {
        return ($this->right === null ? 0 : $this->right->height) - ($this->left === null ? 0 : $this->left->height);
    }

    /**
     * Перерасчёт высоты вершины
     */
    protected function fixHeight (): void
    {
        $leftHeight  = $this->left === null ? 0 : $this->left->height;
        $rightHeight = $this->right === null ? 0 : $this->right->height;

        $this->height = max ($leftHeight, $rightHeight) + 1;
    }

    /**
     * Левый поворот дерева
     * 
     * @return Node - возвращает вершину нового дерева
     */
    protected function rotateLeft (): Node
    {
        $right = $this->right;

        $this->right = $right->left;
        $right->left = $this;

        $this->fixHeight ();
        $right->fixHeight ();

        return $right;
    }

    /**
     * Правый поворот дерева
     * 
     * @return Node - возвращает вершину нового дерева
     */
    protected function rotateRight (): Node
    {
        $left = $this->left;

        $this->left = $left->right;
        $left->right = $this;

        $this->fixHeight ();
        $left->fixHeight ();

        return $left;
    }

    /**
     * Балансировка (сортировка) дерева
     * 
     * @return Node - возвращает вершину нового дерева
     */
    public function balance (): Node
    {
        $this->fixHeight ();

        switch ($this->getBalanceFactor ())
        {
            case 2:
                if ($this->right->getBalanceFactor () < 0)
                    $this->right = $this->right->rotateRight ();

                return $this->rotateLeft ();
            break;

            case -2:
                if ($this->left->getBalanceFactor () > 0)
                    $this->left = $this->left->rotateLeft ();

                return $this->rotateRight ();
            break;
        }

        return $this;
    }

    /**
     * Добавление данных в дерево
     * 
     * @param string $data - данные для добавления
     * 
     * @return Node - возвращает вершину нового дерева
     */
    public function push (string $data): Node
    {
        if ($data < $this->data)
        {
            if ($this->left === null)
                $this->left = new Node ($data);

            else $this->left = $this->left->push ($data);
        }

        elseif ($data > $this->data)
        {
            if ($this->right === null)
                $this->right = new Node ($data);

            else $this->right = $this->right->push ($data);
        }

        return $this->balance ();
    }

    /**
     * Массовое добавление данных
     * 
     * @param array $items - массив элементов для добавления
     * 
     * @return Node - возвращает вершину нового дерева
     */
    public function massPush (array $items): Node
    {
        $node = $this;

        foreach ($items as $item)
            $node = $node->push ($item);

        return $node;
    }

    /**
     * Поиск наименьшего значения в дереве
     * 
     * @return Node - возвращает наименьшую вершину
     */
    protected function findMin (): Node
    {
        return $this->left !== null ?
            $this->left->findMin () : $this;
    }

    /**
     * Удаление наименьшей вершины дерева
     * 
     * @return Node|null - возвращает вершину нового дерева
     */
    protected function popMin (): ?Node
    {
        if ($this->left === null)
            return $this->right;

        $this->left = $this->left->popMin ();

        return $this->balance ();
    }

    /**
     * Удаление вершины дерева
     * 
     * @param string $data - данные для удаления
     * 
     * @return Node|null - возвращает вершину нового дерева или null, если было удалено всё дерево
     */
    public function pop (string $data): ?Node
    {
        if ($data < $this->data)
            $this->left = $this->left->pop ($data);

        elseif ($data > $this->data)
            $this->right = $this->right->pop ($data);

        else
        {
            $left  = $this->left;
            $right = $this->right;

            if ($right === null)
                return $left;

            $min = $right->findMin ();
            $min->right = $right->popMin ();
            $min->left  = $left;

            return $min->balance ();
        }

        return $this->balance ();
    }

    /**
     * Массовое удаление элементов дерева
     * 
     * @param array $items - массив элементов для удаления
     * 
     * @return Node|null - возвращает вершину нового дерева или null, если было удалено всё дерево
     */
    public function massPop (array $items): ?Node
    {
        $node = $this;

        foreach ($items as $item)
            if ($node !== null)
                $node = $node->pop ($item);

            else break;

        return $node;
    }

    /**
     * Поиск вершины в дереве
     * 
     * @param string $data - данные для поиска
     * 
     * @return Node|null - возвращает найденную вершину или null если её не существует
     */
    public function search (string $data): ?Node
    {
        if ($data == $this->data)
            return $this;

        elseif ($data < $this->data)
            return $this->left !== null ?
                $this->left->search ($data) : null;

        elseif ($data > $this->data)
            return $this->right !== null ?
                $this->right->search ($data) : null;

        return null;
    }

    /**
     * Получение полного пути до вершины
     * 
     * @param string $data - данные для поиска
     * [@param array $path = []] - префикс пути
     * 
     * @return array|null - возвращает полный путь до вершины или null если его не существует
     */
    public function getDataPath (string $data, array $path = []): ?array
    {
        $path[] = $this->data;

        if ($data == $this->data)
            return $path;

        elseif ($data < $this->data)
            return $this->left !== null ?
                $this->left->getDataPath ($data, $path) : null;

        elseif ($data > $this->data)
            return $this->right !== null?
                $this->right->getDataPath ($data, $path) : null;

        return null;
    }

    /**
     * Поиск вершины в дереве с компаратором
     * 
     * @param \Closure $comparator - компаратор (анонимная функция) для сравнения вершин (аргумент - Node)
     * 
     * @return Node|null - возвращает найденную вершину или null если таковой не найдено
     */
    public function customSearch (\Closure $comparator): ?Node
    {
        if ($comparator ($this))
            return $this;

        elseif ($this->left !== null)
            return $this->left->customSearch ($comparator);

        elseif ($this->right !== null)
            return $this->right->customSearch ($comparator);

        else return null;
    }

    /**
     * Проход по всем вершинам дерева
     * 
     * @param \Closure $callable - функция для прохода по дереву (аргумент - Node)
     */
    public function foreach (\Closure $callable): void
    {
        $callable ($this);

        if ($this->left !== null)
            $this->left->foreach ($callable);

        if ($this->right !== null)
            $this->right->foreach ($callable);
    }

    /**
     * Получение массива элементов дерева по компаратору
     * 
     * @param \Closure $comparator - компаратор элементов (аргумент - Node)
     * 
     * @return array - возвращает массив подходящих элементов
     */
    public function where (\Closure $comparator): array
    {
        $hips = [];

        if ($comparator ($this))
            $hips[] = $this->data;

        if ($this->left !== null)
            $hips = array_merge ($hips, $this->left->where ($comparator));

        if ($this->right !== null)
            $hips = array_merge ($hips, $this->right->where ($comparator));

        return $hips;
    }

    /**
     * Срез дерева; получение всех вершин
     * 
     * @return array - возвращает многомерный массив всех вершин дерева
     */
    public function splay (): array
    {
        return [
            $this->data => [
                $this->left !== null ? $this->left->splay () : null,
                $this->right !== null ? $this->right->splay () : null
            ]
        ];
    }
}

/**
 * Интерфейс работы с АВЛ-деревом
 */
class Tree
{
    public $tree = null;

    /**
     * Конструктор дерева
     * 
     * [@param array $tree = []] - список элементов для добавления в дерево
     */
    public function __construct (array $tree = [])
    {
        foreach ($tree as $item)
            $this->push ($item);
    }

    /**
     * Добавление данных в дерево
     * 
     * @param string $data - данные для добавления
     * 
     * @return Tree - возвращает само себя
     */
    public function push (string $data): Tree
    {
        if ($this->tree === null)
            $this->tree = new Node ($data);

        else $this->tree = $this->tree->push ($data);

        return $this;
    }

    /**
     * Массовое добавление данных в дерево
     * 
     * @param array $items - список данных для добавления
     * 
     * @return Tree - возвращает само себя
     */
    public function massPush (array $items): Tree
    {
        if ($this->tree === null)
        {
            $this->tree = new Node (current ($items));

            $items = array_slice ($items, 1);
        }
        
        $this->tree->massPush ($items);

        return $this;
    }

    /**
     * Удаление данных из дерева
     * 
     * @param string $data - данные для удаления
     * 
     * @return Tree - возвращает само себя
     */
    public function pop (string $data): Tree
    {
        if ($this->tree !== null)
            $this->tree = $this->tree->pop ($data);

        return $this;
    }

    /**
     * Массовое удаление данных из дерева
     * 
     * @param array $items - список данных для удаления
     * 
     * @return Tree - возвращает само себя
     */
    public function massPop (array $items): Tree
    {
        if ($this->tree !== null)
            $this->tree->massPop ($items);

        return $this;
    }

    /**
     * Поиск данных в дереве
     * 
     * @param string $data - данные для поиска
     * 
     * @return Node|null - возвращает найденную вершину или null если её не существует 
     */
    public function search (string $data): ?Node
    {
        return $this->tree !== null ?
            $this->tree->search ($data) : null;
    }

    /**
     * Получение полного пути до вершины
     * 
     * @param string $data - данные для поиска
     * 
     * @return array|null - возвращает полный путь до вершины или null если его не существует
     */
    public function getDataPath (string $data): ?array
    {
        return $this->tree !== null ?
            $this->tree->getDataPath ($data) : null;
    }

    /**
     * Поиск вершины в дереве с компаратором
     * 
     * @param \Closure $comparator - компаратор для сравнения вершин (аргумент - Node)
     * 
     * @return Node|null - возвращает найденную вершину или null если таковой не найдено
     */
    public function customSearch (\Closure $comparator): ?Node
    {
        return $this->tree !== null ?
            $this->tree->customSearch ($comparator) : null;
    }

    /**
     * Проход по всем вершинам дерева
     * 
     * @param \Closure $callable - функция для прохода по дереву (аргумент - Node)
     */
    public function foreach (\Closure $callable): Tree
    {
        if ($this->tree !== null)
            $this->tree->foreach ($callable);

        return $this;
    }

    /**
     * Получение нового дерева по компаратору
     * 
     * @param \Closure $comparator - компаратор элементов (аргумент - Node)
     * 
     * @return Tree - возвращает новое дерево из найденных элементов
     */
    public function where (\Closure $comparator): Tree
    {
        return $this->tree !== null ?
            new Tree ($this->tree->where ($comparator)) : $this;
    }

    /**
     * Срез дерева; получение всех вершин
     * 
     * @return array - возвращает многомерный массив всех вершин дерева
     */
    public function splay (): array
    {
        return $this->tree !== null ?
            $this->tree->splay () : [];
    }

    /**
     * Получение списка всех вершин дерева
     * 
     * [@param array $splay = null] - срез дерева (см. метод splay)
     * 
     * @return array - возвращает список вершин
     */
    public function getHips (array $splay = null): array
    {
        $hips = [];

        if ($splay === null)
            $splay = $this->splay ();

        foreach ($splay as $node => $subnodes)
        {
            $hips[] = $node;

            if ($subnodes[0] !== null)
                $hips = array_merge ($hips, $this->getHips ($subnodes[0]));
            
            if ($subnodes[1] !== null)
                $hips = array_merge ($hips, $this->getHips ($subnodes[1]));
        }

        return $hips;
    }

    /**
     * Выращивание дерева; добавление вершин из среза
     * 
     * @param array $splay - срез дерева
     * 
     * @return Tree - возвращает новое дерево
     */
    public function grow (array $splay): Tree
    {
        $node = current (array_keys ($splay));
        $this->push ($node);
        
        if ($splay[$node][0] !== null)
            $this->grow ($splay[$node][0]);
        
        if ($splay[$node][1] !== null)
            $this->grow ($splay[$node][1]);

        return $this;
    }

    /**
     * Объединение двух деревьев
     * 
     * @param Tree $tree - дерево для объединения
     * 
     * @return Tree - возвращает новое дерево (self \/ tree)
     */
    public function union (Tree $tree): Tree
    {
        if ($this->tree === null)
            $this->tree = $tree->tree;

        elseif ($tree->tree !== null)
            $this->grow ($tree->splay ());
        
        return $this;
    }
}
