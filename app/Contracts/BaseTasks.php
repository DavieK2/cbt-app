<?php

namespace App\Contracts;

use App\Http\Resources\BaseResource;
use App\Http\Resources\BaseViewResource;
use App\Modules\CBT\Models\QuestionModel;
use App\Modules\UserManager\Constants\UserManagerConstants;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


enum ResponseType 
{
    case JSON;
    case INERTIA;
}

abstract class BaseTasks {

    public function __construct(protected LengthAwarePaginator|Builder|QueryBuilder|Model|Collection|array $item = []){}

    protected function start($data)
    {
        return new static($data);
    }
 
    public static function __callStatic($method, $args)
    {
        return (new static())->$method(...$args);
    }

    public function __call($name, $args)
    {
        return match($name){
            'start' => (new static())->$name(...$args),
            default =>  new static($this->item)
        };
    }

    public function except(array $keys, $recursive = false)
    {
        foreach( $keys as $key ){

            if( is_array($this->item) && array_key_exists($key, $this->item) ){
                unset($this->item[$key]);
            }

            if( is_array( $this->item ) && $recursive ){

                foreach( $this->item as $index => $item ){

                    if( is_array( $item ) && array_key_exists($key, $item)  ){
                        
                        unset( $this->item[$index][$key] );
                    }

                }
            }
        }

        return new static($this->item);
        
    }

    public function only(array $keys)
    {
        $items = [];

        foreach($keys as $key){
            if(is_array($this->item) && array_key_exists($key, $this->item)){
                $items[$key] = $this->item[$key];
            }
        }
        return new static($items);
        
    }

    public function empty()
    {
        return new static([]);
    }

    public function perPage()
    {
        return new static($this->item['query']->paginate($this->item['perPage']));
    }

    public function all()
    {
        return new static( isset( $this->item['query'] ) ? $this->item['query']->get() : $this->item );
    }

    public function toArray()
    {
        if( is_array( $this->item ) ){

            return new static( $this->item );
        }

        return new static( $this->item->toArray() );
    }

    public function get()
    {
        return $this->item;
    }

    public function search()
    {
        $searchableColumns = collect(['first_name', 'last_name', 'dob']);

        return new static($this->item['query']->search($this->prepareSearchData($searchableColumns, $this->item['search'])));
    }
    
    public function filter()
    {
        $data = json_decode($this->item['filter'], true);
        $filterableColumns = ['group_id' => 'integer', 'network_provider' => 'string'];
        
        return new static($this->item['query']->filter($this->prepareFilterData($data, $filterableColumns)));

    }

    public function sort()
    {
        $data = json_decode($this->item['sort'], true);

        $sortableDirections = ['asc', 'desc'];
        $sortableColumns = ['first_name', 'last_name', 'dob', 'network_provider'];
        
        return new static($this->item['query']->sort($this->prepareSortData($data, $sortableColumns, $sortableDirections)));

    }
    
    protected function formatResponse(BaseTasks $builder, ResponseType $reponseType = ResponseType::JSON, array $options = [], string $formatter = "")
    {
        $message = "";
        $status = 200;

        $viewValidOptions = ['view', 'view_data'];
        $filtered_view_data = collect($options)->filter(fn($option, $index) => in_array($index, $viewValidOptions))->toArray();

        if($reponseType == ResponseType::JSON){

            $message = isset($options['message']) ? $options['message'] : $message;
            $status = isset($options['status']) ? $options['status'] : $status;
        }

        if( empty($formatter) && $reponseType == ResponseType::JSON ){
            return new class(empty($message) ? $builder->item : [...$builder->item, 'message' => $message], $status) extends BaseResource {};
        }

        if(empty($formatter) && $reponseType == ResponseType::INERTIA){
            return (new class($filtered_view_data) extends BaseViewResource {})();
        }

        if( ! class_exists($formatter) ){
            throw new Exception('Formatter class does not exist: '.$formatter);
        }

        if($reponseType == ResponseType::JSON){

            return  new $formatter($builder->item);
        }

        if($reponseType == ResponseType::INERTIA){

            return  (new $formatter(UserManagerConstants::LOGIN_VIEW, $filtered_view_data ))();
        }
    }

    protected function prepareSortData(array $data, array $sortableColumns, array $sortableDirections)
    {
        return Validator::make([ 'sort' => $data ], 
        [
            'sort' => [ Rule::prohibitedIf(fn() => ! ( (count($data) == 1 ) && in_array( array_keys($data)[0], $sortableColumns ) && in_array( array_values($data)[0], $sortableDirections ))) ]
        ], 
        [
            'prohibited' => 'The sort request is not valid'
        ])->validate()['sort'];
    }

    protected function prepareSearchData(Collection $searchableColumns, string $searchValue)
    {
        return $searchableColumns->flatMap(fn($column) => [ $column => $searchValue ]);
    }

    protected function prepareFilterData(array $data, array $filterableColumns)
    {
        $validatedData = Validator::make($data, $filterableColumns)->validate();
        
        return empty($validatedData) ? throw ValidationException::withMessages(['filter' => 'Invalid filter data']) : $validatedData ;
    }
}