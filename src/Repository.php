<?php

namespace LuizHenriqueBK\LaravelRepository;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\{Builder, Model};

/**
 * Class Repository
 * @package LuizHenriqueBK\LaravelRepository
 */
abstract class Repository implements RepositoryInterface
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

   /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct()
    {
        $instance = app($this->model);

        if ($instance instanceof Model === false) {
            throw new Exception("Class {$this->model} must be an instance of ".Model::class);
        }

        $this->model = $instance;
    }

    /**
     * Return all results of the given model from the database
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all(array $columns = ['*'])
    {
        if ($this->model instanceof Builder) {
            return $this->model->get($columns);
        }

        return $this->model->all($columns);
    }

    /**
     * Return paginated results of the given model from the database
     *
     * @param int $length
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate(int $length = 15, array $columns = ['*'])
    {
        return $this->model->paginate($length, $columns);
    }

    /**
     * Return a model by ID from the database.
     *
     * @param int $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find(int $id, array $columns = ['*'])
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Return a model by columns from the database.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy(array $criteria = [], array $columns = ['*'], array $orderBy = [], $limit = null, $offset = null)
    {
        $model = $this->model->newQuery();

        if (count($criteria) == 1) {
            $model = $model->where($criteria);
        } elseif (count($criteria > 1)) {
            foreach ($criteria as $c) {
                $model = $model->where($c[0], $c[1]);
            }
        }

        if (count($orderBy) == 1) {
            $model = $model->orderBy($orderBy[0], $orderBy[1]);
        } elseif (count($orderBy) > 1) {
            foreach ($orderBy as $order) {
                $model = $model->orderBy($order[0], $order[1]);
            }
        }

        if (isset($limit)) {
            $model = $model->take((int) $limit);
        }

        if (isset($offset)) {
            $model = $model->skip((int) $offset);
        }

        return $model->get($columns);
    }

    /**
     * Return a model by columns from the database.
     *
     * @param array $criteria
     * @param array $columns
     *
     * @return mixed
     */
    public function findOneBy(array $criteria = [], array $columns = ['*'])
    {
        return $this->findBy($criteria, $columns, [], 1)->first();
    }

    /**
     * @param array $input
     *
     * @return mixed
     */
    public function create(array $input)
    {
        $model = $this->model->fill($input);

        $model->save();

        return $model;
    }

    /**
     * @param int $id
     * @param array $input
     *
     * @return mixed
     */
    public function update(int $id, array $input)
    {
        $model = $this->model->findOrFail($id);

        $model->fill($input)->save();

        return $model;
    }

    /**
     * @param int $id
     *
     * @return boolean
     */
    public function delete(int $id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    /**
     * @param array $values
     *
     * @return boolean
     */
    public function bulkDelete(array $values)
    {
        return $this->model->whereIn($this->model->getQualifiedKeyName(), $values)->delete();
    }

    /**
     * Trigger static method calls to the model
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

    /**
     * Trigger method calls to the model
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 6) == 'findBy') {
            $by = substr($method, 6, strlen($method));
            $method = 'findBy';
        } elseif (substr($method, 0, 9) == 'findOneBy') {
            $by = substr($method, 9, strlen($method));
            $method = 'findOneBy';
        }

        if (isset($by)) {
            if (!isset($arguments[0])) {
                // we dont even want to allow null at this point, because we cannot (yet) transform it into IS NULL.
                throw new Exception('You must have one argument');
            }

            $field = lcfirst($by);
            $value = current($arguments);
            $arguments = Arr::except($arguments, [0]);

            return $this->{$method}([$field, $value], ...$arguments);
        }

        return call_user_func_array([$this->model, $method], $arguments);
    }
}
