<?php

namespace App\Services;

use App\Repositories\CustomerRepository;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class LeysCustomerService
{
    protected $repository;

    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getAllPaginated($perPage);
    }

    public function getDetails(int $id): ?Customer
    {
        $customer = $this->repository->findById($id);
        if (!$customer) {
            throw new \Exception('Customer not found', 404);
        }
        return $customer;
    }

    public function create(array $data): Customer
    {
        // Business logic: Validate category is valid
        $validCategories = ['A', 'A+', 'B', 'C'];
        if (!in_array($data['category'], $validCategories)) {
            throw new \Exception('Invalid customer category', 422);
        }
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Customer
    {
        $customer = $this->getDetails($id);
        $this->repository->update($customer, $data);
        return $customer->fresh();
    }

    public function delete(int $id): void
    {
        $customer = $this->getDetails($id);
        $this->repository->softDelete($customer);
    }

    public function getOrders(int $id, int $perPage = 15): LengthAwarePaginator
    {
        $customer = $this->getDetails($id);
        return $this->repository->getOrders($customer, $perPage);
    }

    public function getCreditStatus(int $id): array
    {
        $customer = $this->getDetails($id);
        return $this->repository->getCreditStatus($customer);
    }

    public function getMapData(): Collection
    {
        return $this->repository->getMapData();
    }
}