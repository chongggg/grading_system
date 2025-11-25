<?php if (!defined('PREVENT_DIRECT_ACCESS')) exit; ?>
<?php include __DIR__ . '/_header.php'; ?>
<div class="p-6 rounded card">
    <h2 class="text-xl font-semibold mb-4">Add Teacher</h2>
    <form method="POST" action="<?= site_url('admin/add_teacher') ?>" class="space-y-4">
        <div>
            <label class="block text-sm text-black-300">First name</label>
            <input type="text" name="first_name" class="w-full p-2 rounded border border-gray-300 text-gray-900" required>
        </div>
        <div>
            <label class="block text-sm text-black-300">Middle name</label>
            <input type="text" name="middle_name" class="w-full p-2 rounded border border-gray-300 text-gray-900">
        </div>
        <div>
            <label class="block text-sm text-black-300">Last name</label>
            <input type="text" name="last_name" class="w-full p-2 rounded border border-gray-300 text-gray-900" required>
        </div>
        <div>
            <label class="block text-sm text-black-300">Email</label>
            <input type="email" name="email" class="w-full p-2 rounded border border-gray-300 text-gray-900" required>
        </div>
        <div>
            <label class="block text-sm text-black-300">Specialization</label>
            <input type="text" name="specialization" class="w-full p-2 rounded border border-gray-300 text-gray-900">
        </div>
        <div>
            <label class="block text-sm text-black-300">Contact Number</label>
            <input type="text" name="contact_number" class="w-full p-2 rounded border border-gray-300 text-gray-900">
        </div>
        <div>
            <label class="block text-sm text-black-300">Username</label>
            <input type="text" name="username" class="w-full p-2 rounded border border-gray-300 text-gray-900" required>
        </div>
        <div>
            <label class="block text-sm text-black-300">Password</label>
            <input type="password" name="password" class="w-full p-2 rounded border border-gray-300 text-gray-900" required>
        </div>
        <div>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Create</button>
            <a href="<?= site_url('admin/teachers') ?>" class="ml-3 text-gray-300">Cancel</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/_footer.php'; ?>