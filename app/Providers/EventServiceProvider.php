<?php

namespace App\Providers;

use App\Events\ProjectUserAttached;
use App\Events\ProspectLabelAttached;
use App\Events\ProspectUserAttached;
use App\Events\UserDeviceCreated;
use App\Listeners\ProjectUserAttachedListener;
use App\Listeners\ProspectLabelAttachedListener;
use App\Listeners\ProspectUserAttachedListener;
use App\Listeners\UserAuthenticatedListener;
use App\Listeners\UserDeviceCreatedListener;
// use App\Models\Calendar;
// use App\Models\Category;
// use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\DocumentPage;
use App\Models\Event;
use App\Models\Field;
use App\Models\File;
use App\Models\Folder;
// use App\Models\Group;
use App\Models\Import;
use App\Models\Interaction;
use App\Models\Invoice;
// use App\Models\Label;
use App\Models\Menu;
use App\Models\Message;
// use App\Models\MessageTemplate;
use App\Models\Ocr;
use App\Models\Order;
// use App\Models\OrderAction;
// use App\Models\OrderStatus;
// use App\Models\OrderStep;
// use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Project;
use App\Models\Prospect;
use App\Models\ProspectQuestionnaireResponse;
// use App\Models\Questionnaire;
use App\Models\QuestionnaireOption;
use App\Models\QuestionnaireQuestion;
use App\Models\QuestionnaireSection;
use App\Models\Sms;
// use App\Models\SmsTemplate;
use App\Models\Thread;
use App\Models\User;
use App\Models\UserSetting;

// use App\Observers\CalendarObserver;
// use App\Observers\CategoryObserver;
// use App\Observers\DocumentObserver;
use App\Observers\DocumentFileObserver;
use App\Observers\DocumentPageObserver;
use App\Observers\EventObserver;
use App\Observers\FieldObserver;
use App\Observers\FileObserver;
use App\Observers\FolderObserver;
// use App\Observers\GroupObserver;
use App\Observers\ImportObserver;
use App\Observers\InteractionObserver;
use App\Observers\InvoiceObserver;
// use App\Observers\LabelObserver;
use App\Observers\MenuObserver;
use App\Observers\MessageObserver;
// use App\Observers\MessageTemplateObserver;
use App\Observers\OcrObserver;
// use App\Observers\OrderActionObserver;
use App\Observers\OrderObserver;
// use App\Observers\OrderStatusObserver;
// use App\Observers\OrderStepObserver;
use App\Observers\ProductImageObserver;
// use App\Observers\ProductObserver;
use App\Observers\ProjectObserver;
use App\Observers\ProspectObserver;
use App\Observers\ProspectQuestionnaireResponseObserver;
// use App\Observers\QuestionnaireObserver;
use App\Observers\QuestionnaireOptionObserver;
use App\Observers\QuestionnaireQuestionObserver;
use App\Observers\QuestionnaireSectionObserver;
use App\Observers\SmsObserver;
// use App\Observers\SmsTemplateObserver;
use App\Observers\ThreadObserver;
use App\Observers\UserObserver;
use App\Observers\UserSettingObserver;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event as LaravelEvent;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserDeviceCreated::class => [
            UserDeviceCreatedListener::class,
        ],
        Authenticated::class => [
            UserAuthenticatedListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Calendar::observe(CalendarObserver::class);
        // Category::observe(CategoryObserver::class);
        DocumentFile::observe(DocumentFileObserver::class);
        // Document::observe(DocumentObserver::class);
        DocumentPage::observe(DocumentPageObserver::class);
        Event::observe(EventObserver::class);
        Field::observe(FieldObserver::class);
        File::observe(FileObserver::class);
        Folder::observe(FolderObserver::class);
        // Group::observe(GroupObserver::class);
        Import::observe(ImportObserver::class);
        Invoice::observe(InvoiceObserver::class);
        Interaction::observe(InteractionObserver::class);
        // Label::observe(LabelObserver::class);
        Menu::observe(MenuObserver::class);
        Message::observe(MessageObserver::class);
        // MessageTemplate::observe(MessageTemplateObserver::class);
        Ocr::observe(OcrObserver::class);
        Order::observe(OrderObserver::class);
        // OrderAction::observe(OrderActionObserver::class);
        // OrderStatus::observe(OrderStatusObserver::class);
        // OrderStep::observe(OrderStepObserver::class);
        // Product::observe(ProductObserver::class);
        ProductImage::observe(ProductImageObserver::class);
        Project::observe(ProjectObserver::class);
        Prospect::observe(ProspectObserver::class);
        ProspectQuestionnaireResponse::observe(ProspectQuestionnaireResponseObserver::class);
        // Questionnaire::observe(QuestionnaireObserver::class);
        QuestionnaireOption::observe(QuestionnaireOptionObserver::class);
        QuestionnaireQuestion::observe(QuestionnaireQuestionObserver::class);
        QuestionnaireSection::observe(QuestionnaireSectionObserver::class);
        Sms::observe(SmsObserver::class);
        // SmsTemplate::observe(SmsTemplateObserver::class);
        Thread::observe(ThreadObserver::class);
        User::observe(UserObserver::class);
        UserSetting::observe(UserSettingObserver::class);
            
        LaravelEvent::listen(
            ProspectLabelAttached::class,
            [ProspectLabelAttachedListener::class, 'handle']
        );
    
        LaravelEvent::listen(
            ProspectUserAttached::class,
            [ProspectUserAttachedListener::class, 'handle']
        );
    
        LaravelEvent::listen(
            ProjectUserAttached::class,
            [ProjectUserAttachedListener::class, 'handle']
        );
    
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
