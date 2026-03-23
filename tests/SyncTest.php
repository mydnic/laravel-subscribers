<?php

namespace Mydnic\Subscribers\Test;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Mydnic\Subscribers\Models\Subscriber;
use Mydnic\Subscribers\Traits\HasNewsletterSubscription;
use PHPUnit\Framework\Attributes\Test;

class FakeUser extends Model
{
    use HasNewsletterSubscription;

    protected $table = 'fake_users';

    protected $fillable = ['email', 'subscribed_to_newsletter'];

    public $timestamps = false;
}

class SyncTest extends TestCase
{
    protected function setUpDatabase(): void
    {
        parent::setUpDatabase();

        Schema::create('fake_users', function ($table) {
            $table->id();
            $table->string('email')->unique();
            $table->boolean('subscribed_to_newsletter')->default(false);
        });
    }

    #[Test]
    public function it_subscribes_user_when_flag_is_true(): void
    {
        FakeUser::create([
            'email' => 'user@example.com',
            'subscribed_to_newsletter' => true,
        ]);

        $this->assertDatabaseHas('subscribers', ['email' => 'user@example.com']);
    }

    #[Test]
    public function it_does_not_subscribe_when_flag_is_false(): void
    {
        FakeUser::create([
            'email' => 'user@example.com',
            'subscribed_to_newsletter' => false,
        ]);

        $this->assertDatabaseMissing('subscribers', ['email' => 'user@example.com']);
    }

    #[Test]
    public function it_unsubscribes_when_flag_is_turned_off(): void
    {
        $user = FakeUser::create([
            'email' => 'user@example.com',
            'subscribed_to_newsletter' => true,
        ]);

        $this->assertDatabaseHas('subscribers', ['email' => 'user@example.com']);

        $user->update(['subscribed_to_newsletter' => false]);

        $this->assertEquals(0, Subscriber::count());
    }

    #[Test]
    public function it_restores_soft_deleted_subscriber_on_re_subscribe(): void
    {
        $user = FakeUser::create([
            'email' => 'user@example.com',
            'subscribed_to_newsletter' => true,
        ]);

        $user->update(['subscribed_to_newsletter' => false]);
        $this->assertEquals(0, Subscriber::count());

        $user->update(['subscribed_to_newsletter' => true]);
        $this->assertEquals(1, Subscriber::count());
    }
}
