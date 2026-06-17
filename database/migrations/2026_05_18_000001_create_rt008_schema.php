<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rt_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('rt_number', 10)->default('008');
            $table->string('rw_number', 10)->nullable();
            $table->string('kelurahan')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('ketua_rt')->nullable();
            $table->string('ketua_rw')->nullable();
            $table->string('sekretaris_rt')->nullable();
            $table->text('alamat_kantor')->nullable();
            $table->timestamps();
        });

        Schema::create('households', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rt_profile_id')->constrained('rt_profiles')->cascadeOnDelete();
            $table->string('family_card_number', 32)->nullable()->unique();
            $table->string('house_number', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });

        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained('households')->cascadeOnDelete();
            $table->string('nik', 16)->nullable()->unique();
            $table->string('name');
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('religion', 30)->nullable();
            $table->string('occupation')->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('citizenship', 30)->default('WNI');
            $table->string('relationship_to_head', 30)->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_head_of_family')->default(false);
            $table->string('domicile_status')->default('aktif');
            $table->boolean('whatsapp_notify')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('warga')->after('email');
            $table->string('phone', 20)->nullable()->after('role');
            $table->foreignId('resident_id')->nullable()->after('phone')->constrained('residents')->nullOnDelete();
        });

        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('required_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('service_type_id')->constrained('service_types');
            $table->foreignId('resident_id')->constrained('residents');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->text('purpose')->nullable();
            $table->json('form_data')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });

        Schema::create('letter_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained('service_types')->cascadeOnDelete();
            $table->string('name');
            $table->longText('body_html');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('generated_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('letter_template_id')->nullable()->constrained('letter_templates')->nullOnDelete();
            $table->string('file_path');
            $table->string('letter_number')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->foreignId('resident_id')->nullable()->constrained('residents')->nullOnDelete();
            $table->string('phone', 20);
            $table->string('event');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->string('whatsapp_message_id')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('generated_letters');
        Schema::dropIfExists('letter_templates');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('service_types');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resident_id');
            $table->dropColumn(['role', 'phone']);
        });

        Schema::dropIfExists('residents');
        Schema::dropIfExists('households');
        Schema::dropIfExists('rt_profiles');
    }
};
