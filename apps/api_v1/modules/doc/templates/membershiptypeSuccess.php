<h1>membershiptype</h1>

<h2>create</h2>
<blockquote><?php echo link_to(url_for('membershiptype/create', true), 'membershiptype/create') ?> (POST)</blockquote> 

<h2>delete</h2>
<blockquote><?php echo link_to(url_for('membershiptype/delete', true).'/[id]', 'membershiptype/delete') ?> (DELETE)</blockquote> 

<h2>list</h2>
<blockquote><?php echo link_to(url_for('membershiptype/index', true), 'membershiptype/index') ?> (GET)</blockquote> 

<h2>show</h2>
<blockquote><?php echo link_to(url_for('membershiptype/show', true).'/[id]', 'membershiptype/show') ?> (GET)</blockquote> 

<h2>update</h2>
<blockquote><?php echo link_to(url_for('membershiptype/update', true).'/[id]', 'membershiptype/update') ?> (PUT)</blockquote> 