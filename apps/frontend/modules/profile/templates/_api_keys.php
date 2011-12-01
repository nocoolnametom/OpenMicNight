<?php if (count($auth_tokens)):?>
<h2>API Auth Tokens</h2>
<table>
    <thead>
        <tr>
            <td>Revoke Key</td>
            <td>Application</td>
            <td>Key Expiration</td>
        </tr>
    </thead>
    <tbody>
<?php foreach($auth_tokens as $token): ?>
<?php /* @var $token sfGuardUserAuthKey */ ?>
    <tr>
    <td><?php echo link_to('Revoke Key', 'profile/auth_revoke?id=' . $token->getIncremented()) ?></td>
    <td><?php echo $token->getApiKey()->getApiAppName() ?></td>
    <td><?php echo $token->getExpiresAt() ?></td>
    </tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>