<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shop;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Offer;
use App\Models\OfferItem;
use App\Models\Delivery;
use App\Models\Customer;
use App\Models\Credit;
use App\Models\CreditTransaction;
use App\Models\NotificationLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComprehensiveTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏪 Creating Shop Owners...');
        $shopOwners = $this->createShopOwners();

        $this->command->info('🚚 Creating Distributors...');
        $distributors = $this->createDistributors();

        $this->command->info('📦 Creating Orders...');
        $orders = $this->createOrders($shopOwners);

        $this->command->info('💰 Creating Offers...');
        $offers = $this->createOffers($orders, $distributors);

        $this->command->info('🚗 Creating Deliveries...');
        $this->createDeliveries($orders, $offers);

        $this->command->info('👥 Creating Customers & Credits...');
        $this->createCustomersAndCredits($shopOwners);

        $this->command->info('🔔 Creating Notifications...');
        $this->createNotifications($shopOwners, $distributors);

        $this->command->info('✅ Comprehensive test data seeded successfully!');
    }

    private function createShopOwners(): array
    {
        $shops = [
            [
                'user' => [
                    'name' => 'Boutique Test',
                    'email' => 'shop@molhanout.ma',
                    'phone' => '+212611111111',
                ],
                'shop' => [
                    'shop_name' => 'Épicerie Al Baraka',
                    'address' => '12 Rue Mohammed V, Casablanca',
                    'city' => 'Casablanca',
                    'latitude' => 33.5731,
                    'longitude' => -7.5898,
                ]
            ],
            [
                'user' => [
                    'name' => 'أحمد المنصوري',
                    'email' => 'ahmed.shop@molhanout.ma',
                    'phone' => '+212612345678',
                ],
                'shop' => [
                    'shop_name' => 'سوبرماركت النجاح',
                    'address' => '45 شارع الحسن الثاني، الرباط',
                    'city' => 'Rabat',
                    'latitude' => 34.0209,
                    'longitude' => -6.8416,
                ]
            ],
            [
                'user' => [
                    'name' => 'Karim Benali',
                    'email' => 'karim.shop@molhanout.ma',
                    'phone' => '+212699887766',
                ],
                'shop' => [
                    'shop_name' => 'Superette Benali',
                    'address' => '78 Avenue Hassan II, Marrakech',
                    'city' => 'Marrakech',
                    'latitude' => 31.6295,
                    'longitude' => -7.9811,
                ]
            ],
        ];

        $shopOwners = [];

        foreach ($shops as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name' => $data['user']['name'],
                    'phone' => $data['user']['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'shop_owner',
                    'is_active' => true,
                    'locale' => 'fr',
                    'email_verified_at' => now(),
                ]
            );

            Shop::firstOrCreate(
                ['user_id' => $user->id],
                array_merge($data['shop'], ['is_verified' => true])
            );

            $shopOwners[] = $user;
        }

        return $shopOwners;
    }

    private function createDistributors(): array
    {
        $distData = [
            [
                'user' => [
                    'name' => 'Distributeur Test',
                    'email' => 'dist@molhanout.ma',
                    'phone' => '+212622222222',
                ],
                'distributor' => [
                    'company_name' => 'Distribution Atlas',
                    'address' => '45 Bd Zerktouni, Casablanca',
                    'city' => 'Casablanca',
                    'latitude' => 33.5890,
                    'longitude' => -7.6100,
                    'service_radius_km' => 50,
                ]
            ],
            [
                'user' => [
                    'name' => 'محمد الفاسي',
                    'email' => 'mohamed.dist@molhanout.ma',
                    'phone' => '+212633445566',
                ],
                'distributor' => [
                    'company_name' => 'توزيع الشمال',
                    'address' => '123 شارع محمد الخامس، فاس',
                    'city' => 'Fes',
                    'latitude' => 34.0181,
                    'longitude' => -5.0078,
                    'service_radius_km' => 80,
                ]
            ],
            [
                'user' => [
                    'name' => 'Hassan Tazi',
                    'email' => 'hassan.dist@molhanout.ma',
                    'phone' => '+212644556677',
                ],
                'distributor' => [
                    'company_name' => 'Tazi Distribution SARL',
                    'address' => '56 Zone Industrielle, Tanger',
                    'city' => 'Tanger',
                    'latitude' => 35.7595,
                    'longitude' => -5.8340,
                    'service_radius_km' => 100,
                ]
            ],
        ];

        $distributors = [];

        foreach ($distData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name' => $data['user']['name'],
                    'phone' => $data['user']['phone'],
                    'password' => Hash::make('password'),
                    'role' => 'distributor',
                    'is_active' => true,
                    'locale' => 'fr',
                    'email_verified_at' => now(),
                ]
            );

            Distributor::firstOrCreate(
                ['user_id' => $user->id],
                array_merge($data['distributor'], ['is_verified' => true])
            );

            $distributors[] = $user;
        }

        return $distributors;
    }

    private function createOrders(array $shopOwners): array
    {
        $products = Product::all();
        $orders = [];
        // Actual status values from DB: 'draft','published','receiving_offers','accepted','preparing','on_delivery','delivered','cancelled'
        $statuses = ['draft', 'published', 'receiving_offers', 'accepted', 'preparing', 'on_delivery', 'delivered', 'cancelled'];

        foreach ($shopOwners as $shopOwner) {
            $shop = $shopOwner->shop;
            
            // Create multiple orders for each shop
            for ($i = 0; $i < 5; $i++) {
                $status = $statuses[$i % count($statuses)];
                
                $order = Order::create([
                    'shop_id' => $shop->id,
                    'user_id' => $shopOwner->id,
                    'status' => $status,
                    'delivery_address' => $shop->address,
                    'delivery_latitude' => $shop->latitude,
                    'delivery_longitude' => $shop->longitude,
                    'notes' => $i % 2 == 0 ? 'ملاحظات الطلب - Order notes' : null,
                    'total_amount' => 0,
                    'published_at' => in_array($status, ['published', 'receiving_offers', 'accepted', 'preparing', 'on_delivery', 'delivered']) ? now()->subDays(rand(1, 10)) : null,
                    'accepted_at' => in_array($status, ['accepted', 'preparing', 'on_delivery', 'delivered']) ? now()->subDays(rand(1, 5)) : null,
                    'delivered_at' => $status === 'delivered' ? now()->subHours(rand(1, 48)) : null,
                ]);

                // Add 2-5 items to each order
                $total = 0;
                $orderProducts = $products->random(rand(2, min(5, $products->count())));
                
                foreach ($orderProducts as $product) {
                    $quantity = rand(5, 50);
                    $unitPrice = $product->reference_price ?? rand(10, 100);
                    $subtotal = $quantity * $unitPrice;
                    $total += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit' => $product->unit ?? 'pcs',
                        'notes' => rand(0, 1) ? 'تفضيل معين' : null,
                    ]);
                }

                $order->update(['total_amount' => $total]);
                $orders[] = $order;
            }
        }

        return $orders;
    }

    private function createOffers(array $orders, array $distributors): array
    {
        $offers = [];
        
        // Create offers for published/receiving_offers/accepted/preparing/on_delivery/delivered orders
        $eligibleStatuses = ['published', 'receiving_offers', 'accepted', 'preparing', 'on_delivery', 'delivered'];
        $eligibleOrders = collect($orders)->filter(fn($o) => in_array($o->status, $eligibleStatuses));

        foreach ($eligibleOrders as $order) {
            // 1-3 offers per order
            $numOffers = rand(1, min(3, count($distributors)));
            $selectedDistributors = collect($distributors)->random($numOffers);

            foreach ($selectedDistributors as $index => $distUser) {
                $distributor = $distUser->distributor;
                // First offer is accepted for orders past receiving_offers status
                $isAccepted = !in_array($order->status, ['published', 'receiving_offers']) && $index === 0;
                
                // Calculate subtotal from order items
                $subtotal = 0;
                $offerItemsData = [];
                
                foreach ($order->items as $orderItem) {
                    $unitPrice = ($orderItem->product->reference_price ?? 50) * (0.8 + (rand(0, 40) / 100));
                    $itemSubtotal = $orderItem->quantity * $unitPrice;
                    $subtotal += $itemSubtotal;
                    
                    $offerItemsData[] = [
                        'order_item_id' => $orderItem->id,
                        'product_id' => $orderItem->product_id,
                        'unit_price' => round($unitPrice, 2),
                        'quantity' => $orderItem->quantity,
                        'subtotal' => round($itemSubtotal, 2),
                        'is_available' => rand(0, 10) > 1, // 90% available
                    ];
                }
                
                $deliveryCost = rand(20, 100);
                
                $offer = Offer::create([
                    'order_id' => $order->id,
                    'distributor_id' => $distributor->id,
                    'user_id' => $distUser->id,
                    'status' => $isAccepted ? 'accepted' : 'submitted',
                    'subtotal' => round($subtotal, 2),
                    'delivery_cost' => $deliveryCost,
                    'total_amount' => round($subtotal + $deliveryCost, 2),
                    'estimated_delivery_time' => rand(1, 48) . ' heures',
                    'estimated_delivery_date' => now()->addHours(rand(2, 48)),
                    'notes' => rand(0, 1) ? 'يمكن التوصيل في الصباح الباكر' : null,
                    'is_cheapest' => $index === 0,
                    'is_fastest' => $index === 0,
                ]);

                // Create offer items
                foreach ($offerItemsData as $itemData) {
                    OfferItem::create(array_merge(['offer_id' => $offer->id], $itemData));
                }

                // Update order's accepted_offer_id if this is the accepted offer
                if ($isAccepted) {
                    $order->update(['accepted_offer_id' => $offer->id]);
                }
                
                // Update order's offer_count
                $order->increment('offer_count');

                $offers[] = $offer;
            }
        }

        return $offers;
    }

    private function createDeliveries(array $orders, array $offers): void
    {
        // status enum: 'preparing','on_the_way','delivered','failed'
        $deliveryOrders = collect($orders)->filter(fn($o) => in_array($o->status, ['preparing', 'on_delivery', 'delivered']));

        foreach ($deliveryOrders as $order) {
            $acceptedOffer = collect($offers)->first(fn($o) => $o->order_id === $order->id && $o->status === 'accepted');
            
            if (!$acceptedOffer) continue;

            $deliveryStatus = match($order->status) {
                'preparing' => 'preparing',
                'on_delivery' => 'on_the_way',
                'delivered' => 'delivered',
                default => 'preparing'
            };
            
            Delivery::create([
                'order_id' => $order->id,
                'offer_id' => $acceptedOffer->id,
                'distributor_id' => $acceptedOffer->distributor_id,
                'status' => $deliveryStatus,
                'confirmation_pin' => str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
                'qr_code' => Str::uuid()->toString(),
                'is_confirmed' => $deliveryStatus === 'delivered',
                'picked_up_at' => in_array($deliveryStatus, ['on_the_way', 'delivered']) ? now()->subHours(rand(1, 5)) : null,
                'delivered_at' => $deliveryStatus === 'delivered' ? now()->subMinutes(rand(30, 120)) : null,
                'current_latitude' => $order->delivery_latitude + (rand(-100, 100) / 10000),
                'current_longitude' => $order->delivery_longitude + (rand(-100, 100) / 10000),
                'delivery_notes' => rand(0, 1) ? 'تم التسليم بنجاح' : null,
            ]);
        }
    }

    private function createCustomersAndCredits(array $shopOwners): void
    {
        $customerNames = [
            'عبد الله الحسني', 'فاطمة الزهراء', 'يوسف المنصوري', 
            'خديجة العلوي', 'محمد أمين', 'سارة بنعلي',
            'Omar Benkirane', 'Laila Tazi', 'Rachid Alami',
        ];

        foreach ($shopOwners as $shopOwner) {
            $shop = $shopOwner->shop;
            
            // Create 2-4 customers per shop
            $numCustomers = rand(2, 4);
            for ($i = 0; $i < $numCustomers; $i++) {
                $customer = Customer::create([
                    'shop_id' => $shop->id,
                    'name' => $customerNames[array_rand($customerNames)] . ' ' . rand(1, 99),
                    'phone' => '+2126' . rand(10000000, 99999999),
                    'address' => 'شارع ' . rand(1, 100) . '، المدينة',
                    'total_debt' => 0,
                    'notes' => rand(0, 1) ? 'زبون منتظم' : null,
                    'is_active' => true,
                ]);

                // Create credits for some customers
                if (rand(0, 1)) {
                    $creditAmount = rand(100, 2000);
                    $paidAmount = $creditAmount * (rand(0, 70) / 100);
                    $status = $paidAmount == 0 ? 'pending' : ($paidAmount >= $creditAmount ? 'paid' : 'partial');
                    
                    $credit = Credit::create([
                        'customer_id' => $customer->id,
                        'shop_id' => $shop->id,
                        'description' => 'دين على البضاعة - شراء بتاريخ ' . now()->subDays(rand(1, 30))->format('Y-m-d'),
                        'amount' => $creditAmount,
                        'status' => $status,
                        'paid_amount' => round($paidAmount, 2),
                        'due_date' => now()->addDays(rand(7, 60)),
                        'notes' => rand(0, 1) ? 'يدفع بالتقسيط' : null,
                    ]);

                    // Update customer total_debt
                    $remainingDebt = $creditAmount - $paidAmount;
                    $customer->update(['total_debt' => $remainingDebt]);

                    // Add payment transactions if there are payments
                    if ($paidAmount > 0) {
                        $numPayments = rand(1, 3);
                        $remainingToPay = $paidAmount;
                        
                        for ($j = 0; $j < $numPayments && $remainingToPay > 0; $j++) {
                            $paymentAmount = $j == $numPayments - 1 ? $remainingToPay : rand(50, min(300, $remainingToPay));
                            $remainingToPay -= $paymentAmount;
                            
                            CreditTransaction::create([
                                'credit_id' => $credit->id,
                                'customer_id' => $customer->id,
                                'shop_id' => $shop->id,
                                'type' => 'payment',
                                'amount' => $paymentAmount,
                                'description' => 'دفعة نقدية',
                                'payment_method' => ['cash', 'bank_transfer', 'mobile_payment'][rand(0, 2)],
                            ]);
                        }
                    }
                    
                    // Add initial debt transaction
                    CreditTransaction::create([
                        'credit_id' => $credit->id,
                        'customer_id' => $customer->id,
                        'shop_id' => $shop->id,
                        'type' => 'debt',
                        'amount' => $creditAmount,
                        'description' => 'دين جديد',
                        'payment_method' => null,
                    ]);
                }
            }
        }
    }

    private function createNotifications(array $shopOwners, array $distributors): void
    {
        $allUsers = array_merge($shopOwners, $distributors);
        
        $notificationTypes = [
            ['type' => 'order_created', 'title' => 'طلب جديد', 'body' => 'تم إنشاء طلب جديد رقم #{{order_id}}'],
            ['type' => 'offer_received', 'title' => 'عرض جديد', 'body' => 'تلقيت عرضاً جديداً على طلبك'],
            ['type' => 'offer_accepted', 'title' => 'تم قبول العرض', 'body' => 'تم قبول عرضك على الطلب #{{order_id}}'],
            ['type' => 'delivery_started', 'title' => 'بدأ التوصيل', 'body' => 'الموزع في طريقه إليك'],
            ['type' => 'delivery_completed', 'title' => 'تم التسليم', 'body' => 'تم توصيل طلبك بنجاح'],
            ['type' => 'payment_reminder', 'title' => 'تذكير بالدفع', 'body' => 'لديك دين مستحق'],
        ];

        foreach ($allUsers as $user) {
            $numNotifs = rand(2, 5);
            for ($i = 0; $i < $numNotifs; $i++) {
                $notif = $notificationTypes[array_rand($notificationTypes)];
                NotificationLog::create([
                    'user_id' => $user->id,
                    'type' => $notif['type'],
                    'title' => $notif['title'],
                    'body' => str_replace('{{order_id}}', rand(1, 20), $notif['body']),
                    'data' => json_encode(['order_id' => rand(1, 20)]),
                    'is_read' => rand(0, 1),
                    'created_at' => now()->subHours(rand(1, 168)),
                ]);
            }
        }
    }
}
