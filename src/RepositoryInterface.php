<?php

namespace LuizHenriqueBK\LaravelRepository;

interface RepositoryInterface
{   
    public function all(array $columns = ['*']);

    public function paginate(int $length = 15, array $columns = ['*']);

    public function find(int $id, array $columns = ['*']);

    public function findBy(array $criteria = [], array $columns = ['*'], array $orderBy = [], $limit = null, $offset = null);

    public function findOneBy(array $criteria = [], array $columns = ['*']);

    public function create(array $input);

    public function update(int $id, array $input);

    public function delete(int $id);

    public function bulkDelete(array $values);
}
