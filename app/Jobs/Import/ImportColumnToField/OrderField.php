<?php

namespace App\Jobs\Import\ImportColumnToField;

use App\Jobs\Import\ImportColumnToFieldInterface;
use App\Models\Project;

class OrderField implements ImportColumnToFieldInterface
{
    protected Project $project;
    protected $products;
    protected $orderActions;
    protected $orderSteps;
    protected $userRepository;
    
    public function __construct(Project $project, UserRepository $userRepository)
    {
        $this->project = $project;
        $this->products = $this->project->products;
        $this->orderActions = $this->project->orderActions;
        $this->orderSteps = $this->project->orderSteps;
        $this->userRepository = $userRepository;
    }

    /**
     * 
     */
    public function handle(&$prospect, $field, $value)
    {
        try {
            $data = json_decode($value, true);

            if (!is_array($data)) {
                return;
            }

            $prospect['orders'] = array_map(function($order) {
                return [
                    'date' => $order['date'],

                    'name' => implode(', ', array_map(function($product) {
                        return $product['name'];
                    }, $order['products'])),

                    // Products
                    'products' => array_map(function($p) {
                        $product = $this->findOrCreateProduct($p);

                        return [
                            'id'            => $product['id'],
                            'currency'      => $p['pivot']['currency'],
                            'price'         => $p['pivot']['price'],
                            'tax'           => $p['pivot']['tax'],
                            'including_tax' => $p['pivot']['including_tax'],
                            'quantity'      => $p['pivot']['quantity'],
                        ];
                    }, $order['products']),

                    // Steps
                    'steps' => array_map(function($s) {
                        return $this->findOrCreateOrderStep($s)->id;
                    }, $order['steps']),

                    // Actions
                    'actions' => array_map(function($a) {
                        $orderAction = $this->findOrCreateOrderAction($a['action']);
                        $orderActionUser = $this->userRepository->findOrCreateUser($a['user']);
                        
                        return [
                            'action' => $orderAction->id,
                            'user'   => $orderActionUser->id,
                        ];
                    }, $order['actions']),
                ];
            }, $data);
        } catch (\Exception $e) {}
    }

    /**
     * 
     */
    protected function findOrCreateProduct(&$p)
    {
        if (!($product = $this->products->where('name', $p['name'])->first())) {
            $product = $this->project->products()->create([
                'name' => $p['name'],
                'description' => $p['description'],
                'currency' => $p['currency'],
                'price' => $p['price'],
                'tax' => $p['tax'],
                'including_tax' => $p['including_tax']
            ]);

            $this->products->push($product);
        }

        return $product;
    }

    /**
     * 
     */
    protected function findOrCreateOrderStep(&$s)
    {
        if (!($orderStep = $this->orderSteps->where('name', $s['name'])->first())) {
            $orderStep = $this->project->orderSteps()->create([
                'name' => $s['name'],
            ]);

            $this->orderSteps->push($orderStep);
        }

        return $orderStep;
    }

    /**
     * 
     */
    protected function findOrCreateOrderAction(&$a)
    {
        if (!($orderAction = $this->orderActions->where('name', $a['name'])->first())) {
            $orderAction = $this->project->orderActions()->create([
                'name' => $a['name'],
                'description' => $a['description']
            ]);

            $this->orderActions->push($orderAction);
        }

        return $orderAction;
    }
}