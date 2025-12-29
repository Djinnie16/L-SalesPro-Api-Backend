<?php
namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerRepository
{
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::withTrashed()->paginate($perPage); // Include soft-deleted for admins if needed
    }

    public function findById(int $id): ?Customer
    {
        return Customer::with('orders')->find($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): bool
    {
        return $customer->update($data);
    }

    public function softDelete(Customer $customer): bool
    {
        return $customer->delete();
    }

    public function getMapData(): Collection
    {
        return Customer::select('id', 'name', 'latitude', 'longitude')->whereNotNull('latitude')->get();
    }

    public function getCreditStatus(Customer $customer): array
    {
        return [
            'credit_limit' => $customer->credit_limit,
            'current_balance' => $customer->current_balance,
            'available_credit' => $customer->available_credit,
        ];
    }

    public function getOrders(Customer $customer, int $perPage = 15): LengthAwarePaginator
    {
        return $customer->orders()->paginate($perPage);
    }
}