
<?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php ($user= $conv->sender_type == 'admin' ? $conv->receiver :  $conv->sender); ?>
    <?php if($user): ?>
    <?php ($unchecked=($conv->last_message?->sender_id == $user->id) ? $conv->unread_message_count : 0); ?>

    <div class="chat-user-info d-flex  p-3 align-items-center customer-list view-admin-conv <?php echo e($unchecked ? 'new-msg ' : ''); ?> <?php echo e($unchecked ? 'conv-active' : ''); ?>" onclick="viewAdminConvs('<?php echo e(route('admin.message.view',['conversation_id'=>$conv->id,'user_id'=>$user->id])); ?>','customer-<?php echo e($user->id); ?>','<?php echo e($conv->id); ?>','<?php echo e($user->id); ?>')"
            id="customer-<?php echo e($user->id); ?>">
            <div class="chat-user-info-img d-none d-md-block">
                <img class="avatar-img onerror-image"
                     src="<?php echo e($user['image_full_url']); ?>"
                     data-onerror-image="<?php echo e(dynamicAsset('public/assets/admin')); ?>/img/160x160/img1.jpg"
                     alt="Image Description">
            </div>
            <div class="chat-user-info-content">
                <h5 class="mb-0 d-flex justify-content-between">
                    <span class=" mr-3"><?php echo e($user['f_name'].' '.$user['l_name']); ?></span> <span
                        class="<?php echo e($unchecked ? 'badge badge-info' : ''); ?>"><?php echo e($unchecked ? $unchecked : ''); ?></span>
                        <small>
                        <?php echo e(Carbon\Carbon::parse($conv->last_message?->created_at)->diffForHumans()); ?>

                            </small>
                </h5>
                <small> <?php echo e($user['phone']); ?></small>
                <div class="text-title"><?php echo e($conv->last_message?->message ? Str::limit($conv->last_message?->message ??'', 35, '...') : (count($conv->last_message?->file_full_url ?? []) > 0 ?  count($conv->last_message?->file_full_url ?? []) .' '. translate('messages.Attachments') :'' )); ?></div>
            </div>
        </div>
    <?php else: ?>
        <div
            class="chat-user-info d-flex  p-3 align-items-center customer-list">
            <div class="chat-user-info-img d-none d-md-block">
                <img class="avatar-img"
                        src='<?php echo e(dynamicAsset('public/assets/admin')); ?>/img/160x160/img1.jpg'
                        alt="Image Description">
            </div>
            <div class="chat-user-info-content">
                <h5 class="mb-0 d-flex justify-content-between">
                    <span class=" mr-3"><?php echo e(translate('messages.user_not_found')); ?></span>
                </h5>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<script src="<?php echo e(dynamicAsset('public/assets/admin')); ?>/js/view-pages/common.js"></script>

<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/admin-views/messages/data.blade.php ENDPATH**/ ?>