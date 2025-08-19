<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'session_id' => null,
            'order_number' => Order::generateOrderNumber(),
            'status' => $this->faker->randomElement(['pending', 'processing', 'shipped', 'delivered', 'cancelled']),
            'subtotal' => $this->faker->randomFloat(2, 50, 500),
            'tax' => $this->faker->randomFloat(2, 10, 100),
            'shipping' => $this->faker->randomFloat(2, 0, 20),
            'total' => $this->faker->randomFloat(2, 60, 600),
            'notes' => $this->faker->optional()->sentence(),
            'tracking_number' => $this->faker->optional()->regexify('TRK[0-9]{9}'),
            'shipping_name' => $this->faker->name(),
            'shipping_email' => $this->faker->safeEmail(),
            'shipping_phone' => $this->faker->phoneNumber(),
            'shipping_address' => $this->faker->streetAddress(),
            'shipping_city' => $this->faker->city(),
            'shipping_state' => $this->faker->state(),
            'shipping_postal_code' => $this->faker->postcode(),
            'shipping_country' => $this->faker->randomElement(['Finland', 'Sweden', 'Norway', 'Denmark', 'Iceland']),
            'billing_same_as_shipping' => true,
            'billing_name' => null,
            'billing_email' => null,
            'billing_phone' => null,
            'billing_address' => null,
            'billing_city' => null,
            'billing_state' => null,
            'billing_postal_code' => null,
            'billing_country' => null,
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal']),
            'payment_status' => 'paid',
            'payment_transaction_id' => $this->faker->regexify('TXN[0-9]{10}'),
            'shipping_service' => $this->faker->randomElement(['DHL', 'FedEx', 'UPS']),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }

    /**
     * Indicate that the order is shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
            'tracking_number' => $this->faker->regexify('TRK[0-9]{9}'),
        ]);
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'tracking_number' => $this->faker->regexify('TRK[0-9]{9}'),
        ]);
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}

