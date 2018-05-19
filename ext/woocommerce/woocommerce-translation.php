<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_translation_gettext_woocommerce( $translation, $text ) {
	switch ( $text ) {
		case 'SKU:':
			$translation = 'Mã sản phẩm:';
			break;
		case 'View Cart':
			$translation = 'Xem giỏ hàng';
			break;
		case 'Order Received':
			$translation = 'Đặt hàng thành công';
			break;
		case 'Thank you. Your order has been received.':
			$translation = 'Xin cảm ơn, đơn hàng của bạn đã được lưu vào hệ thống.';
			break;
		case 'Order Number:':
			$translation = 'Mã đơn hàng:';
			break;
		case 'Date:':
			$translation = 'Ngày:';
			break;
		case 'Payment Method:':
			$translation = 'Phương thức thanh toán:';
			break;
		case 'Our Bank Details':
			$translation = 'Thông tin chuyển khoản';
			break;
		case 'Order Details':
			$translation = 'Chi tiết đơn hàng';
			break;
		case 'Products':
		case 'Product':
			$translation = 'Sản phẩm';
			break;
		case 'Total:':
			$translation = 'Tổng cộng:';
			break;
		case 'Totals':
		case 'Total':
			$translation = 'Tổng';
			break;
		case 'Price':
			$translation = 'Giá';
			break;
		case 'Quantity':
			$translation = 'Số lượng';
			break;
		case 'Coupon code':
			$translation = 'Mã giảm giá';
			break;
		case 'Apply Coupon':
			$translation = 'Áp dụng mã giảm giá';
			break;
		case 'Coupon has been removed.':
			$translation = 'Mã giảm giá đã được xóa.';
			break;
		case 'Please enter a coupon code.':
			$translation = 'Xin vui lòng nhập mã giảm giá.';
			break;
		case 'Cart Totals':
			$translation = 'Tổng cộng giỏ hàng';
			break;
		case 'Update Cart':
			$translation = 'Cập nhật giỏ hàng';
			break;
		case 'Proceed to Checkout':
			$translation = 'Tiến hành thanh toán';
			break;
		case 'Place order':
			$translation = 'Đặt hàng';
			break;
		case 'Your order':
			$translation = 'Đơn hàng của bạn';
			break;
		case 'Postcode / ZIP':
			$translation = 'Mã bưu chính';
			break;
		case 'Town / City':
			$translation = 'Tỉnh / Thành phố';
			break;
		case 'State / County':
			$translation = 'Quận / Huyện';
			break;
		case 'Address':
			$translation = 'Địa chỉ';
			break;
		case 'Save Address':
			$translation = 'Lưu địa chỉ';
			break;
		case 'Edit Address':
			$translation = 'Chỉnh sửa địa chỉ';
			break;
		case 'My Address':
			$translation = 'Địa chỉ của tôi';
			break;
		case 'The following addresses will be used on the checkout page by default.':
			$translation = 'Địa chỉ phía bên dưới mặc định sẽ được áp dụng khi thanh toán.';
			break;
		case 'Order':
			$translation = 'Đơn hàng';
			break;
		case 'Date':
			$translation = 'Ngày';
			break;
		case 'Status':
			$translation = 'Trạng thái';
			break;
		case 'View':
			$translation = 'Xem';
			break;
		case 'Edit':
			$translation = 'Chỉnh sửa';
			break;
		case 'On Hold':
			$translation = 'Đang chờ xử lý';
			break;
		case 'Recent Orders':
			$translation = 'Đơn hàng gần đây';
			break;
		case 'Hello <strong>%1$s</strong> (not %1$s? <a href="%2$s">Sign out</a>).':
			$translation = 'Xin chào <strong>%1$s</strong> (không phải %1$s? <a href="%2$s">Thoát</a>).';
			break;
		case 'From your account dashboard you can view your recent orders, manage your shipping and billing addresses and <a href="%s">edit your password and account details</a>.':
			$translation = 'Bạn có thể xem thông tin lịch sử các đơn hàng gần đây, quản lý địa chỉ thanh toán, địa chỉ giao nhận hàng và <a href="%s">chỉnh sửa thông tin tài khoản</a> trên trang này.';
			break;
		case 'Country':
			$translation = 'Quốc gia';
			break;
		case 'Have a coupon?':
			$translation = 'Có mã giảm giá?';
			break;
		case 'Click here to enter your code':
			$translation = 'Nhấn vào đây để nhập mã của bạn';
			break;
		case 'Subtotal':
			$translation = 'Tạm tính';
			break;
		case 'Subtotal:':
			$translation = 'Tạm tính:';
			break;
		case 'Shipping:':
			$translation = 'Phí vận chuyển:';
			break;
		case 'Customer details':
		case 'Customer Details':
			$translation = 'Thông tin khách hàng';
			break;
		case 'Note:':
			$translation = 'Ghi chú:';
			break;
		case 'Company Name':
			$translation = 'Tên công ty';
			break;
		case 'Email Address':
			$translation = 'Địa chỉ email';
			break;
		case 'Phone':
			$translation = 'Điện thoại';
			break;
		case 'Tel:':
		case 'Telephone:':
			$translation = 'Điện thoại:';
			break;
		case 'Additional Information':
			$translation = 'Thông tin tùy chọn';
			break;
		case 'First Name':
			$translation = 'Tên';
			break;
		case 'Last Name':
			$translation = 'Họ';
			break;
		case 'Order Notes':
			$translation = 'Ghi chú đơn hàng';
			break;
		case 'Billing Details':
			$translation = 'Thông tin thanh toán';
			break;
		case 'Billing address':
		case 'Billing Address':
			$translation = 'Địa chỉ thanh toán';
			break;
		case 'Cart':
			$translation = 'Giỏ hàng';
			break;
		case 'Your cart is currently empty.':
			$translation = 'Hiện tại giỏ hàng của bạn đang trống.';
			break;
		case 'Return To Shop':
			$translation = 'Quay lại gian hàng';
			break;
		case 'Cart updated.':
			$translation = 'Giỏ hàng đã được cập nhật.';
			break;
		case '%s removed. %sUndo?%s':
			$translation = '%s đã được xóa. %sHoàn tác?%s';
			break;
		case '%s removed.':
			$translation = '%s đã được xóa.';
			break;
		case 'Coupon "%s" does not exist!':
			$translation = 'Mã giảm giá "%s" không tồn tại!';
			break;
		case 'Coupon does not exist!':
			$translation = 'Mã giảm giá không tồn tại!';
			break;
		case 'This coupon has expired.':
			$translation = 'Mã giảm giá đã hết hạn.';
			break;
		case 'Coupon code applied successfully.':
			$translation = 'Mã giảm giá đã được áp dụng thành công.';
			break;
		case 'Coupon code already applied!':
			$translation = 'Mã giảm giá đã được áp dụng.';
			break;
		case 'Coupon:':
			$translation = 'Mã giảm giá:';
			break;
		case '[Remove]':
			$translation = '[Xóa]';
			break;
		case 'There are no reviews yet.':
			$translation = 'Hiện chưa có nhận xét nào.';
			break;
		case 'Be the first to review &ldquo;%s&rdquo;':
			$translation = 'Hãy trở thành người đầu tiên gửi nhận xét cho &ldquo;%s&rdquo;';
			break;
		case 'Reviews':
			$translation = 'Nhận xét';
			break;
		case 'Reviews (%d)':
			$translation = 'Nhận xét (%d)';
			break;
		case 'Description':
			$translation = 'Mô tả';
			break;
		case 'Product Description':
			$translation = 'Mô tả sản phẩm';
			break;
		case 'Related Products':
			$translation = 'Sản phẩm liên quan';
			break;
		case 'Submit':
			$translation = 'Gửi';
			break;
		case 'Your Review':
			$translation = 'Nhận xét của bạn';
			break;
		case 'Your Rating':
			$translation = 'Đánh giá của bạn';
			break;
		case 'Add a review':
			$translation = 'Thêm nhận xét';
			break;
		case 'Rated %d out of 5':
		case 'Rated %s out of 5':
			$translation = 'Được đánh giá %s trên tổng số 5';
			break;
		case 'Rate&hellip;':
			$translation = 'Đánh giá&hellip;';
			break;
		case 'Perfect':
			$translation = 'Hoàn hảo';
			break;
		case 'Good':
			$translation = 'Tốt';
			break;
		case 'Average':
			$translation = 'Trung bình';
			break;
		case 'Not that bad':
			$translation = 'Không tệ';
			break;
		case 'Very Poor':
			$translation = 'Rất tệ';
			break;
		case 'Choose an option':
			$translation = 'Chọn tùy chọn';
			break;
		case 'Clear':
			$translation = 'Xóa';
			break;
		case 'Name':
			$translation = 'Tên';
			break;
		case 'Create an account?':
			$translation = 'Tạo tài khoản?';
			break;
		case 'Returning customer?':
			$translation = 'Đã có tài khoản?';
			break;
		case 'Click here to login':
			$translation = 'Nhấn vào đây để đăng nhập';
			break;
		case 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer, please proceed to the Billing &amp; Shipping section.':
		case 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer, please proceed to the Billing & Shipping section.':
			$translation = 'Nếu bạn đã mua hàng trước đó, xin vui lòng nhập thông tin của bạn vào ô bên dưới. Nếu bạn lần đầu tiên mua hàng, xin vui lòng điền thông tin của bạn phía bên dưới.';
			break;
		case 'Username or email address':
		case 'Username or email':
			$translation = 'Tên tài khoản hoặc email';
			break;
		case 'Password':
			$translation = 'Mật khẩu';
			break;
		case 'Remember me':
			$translation = 'Nhớ đăng nhập';
			break;
		case 'Login':
			$translation = 'Đăng nhập';
			break;
		case 'Lost your password?':
			$translation = 'Đã quên mật khẩu?';
			break;
		case 'Lost Password':
			$translation = 'Quên mật khẩu';
			break;
		case 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.':
			$translation = 'Bạn đã quên mật khẩu? Xin vui lòng nhập địa chỉ email hoặc tên tài khoản. Bạn sẽ nhận thông tin để tạo mật khẩu mới thông qua địa chỉ email đã đăng ký.';
			break;
		case 'Reset Password':
			$translation = 'Khôi phục mật khẩu';
			break;
		case 'Enter a username or e-mail address.':
			$translation = 'Nhập tên tài khoản hoặc địa chỉ email.';
			break;
		case 'Invalid username or e-mail.':
			$translation = 'Tên tài khoản hoặc địa chỉ email không đúng.';
			break;
		case 'Username is required.':
			$translation = 'Tên tài khoản là bắt buộc.';
			break;
		case 'ERROR':
		case 'Error':
			$translation = 'Lỗi';
			break;
		case 'Password is required.':
			$translation = 'Mật khẩu là bắt buộc.';
			break;
		case 'Invalid username.':
			$translation = 'Tên tài khoản không đúng.';
			break;
		case 'A user could not be found with this email address.':
			$translation = 'Không tìm thấy tài khoản với địa chỉ email này.';
			break;
		case 'Register':
			$translation = 'Đăng ký';
			break;
		case 'Email address':
			$translation = 'Địa chỉ email';
			break;
		case 'You are now logged in as <strong>%s</strong>':
			$translation = 'Bạn đang đăng nhập với tên tài khoản <strong>%s</strong>';
			break;
		case 'Pay for order';
		case 'Pay for Order':
			$translation = 'Thanh toán cho đơn hàng';
			break;
		case 'Qty':
			$translation = 'Số lượng';
			break;
		case 'This order&rsquo;s status is &ldquo;%s&rdquo;&mdash;it cannot be paid for. Please contact us if you need assistance.':
			$translation = 'Không thể thanh toán cho đơn hàng với trạng thái &ldquo;%s&rdquo;. Xin vui lòng liên hệ với chúng tôi nếu bạn cần sự trợ giúp.';
			break;
		case 'Select product options before adding this product to your cart.':
			$translation = 'Lựa chọn tùy chọn của sản phẩm trước khi thêm vào giỏ hàng.';
			break;
		case 'You have received an order from %s.':
			$translation = 'Bạn vừa nhận được đơn hàng từ %s.';
			break;
		case 'You have received an order from %s. The order is as follows:':
			$translation = 'Bạn vừa nhận được đơn hàng từ %s. Thông tin chi tiết như bên dưới:';
			break;
		case 'Discount:':
			$translation = 'Chiết khấu:';
			break;
		case 'Account Number':
			$translation = 'Số tài khoản';
			break;
		case 'Sort Code':
			$translation = 'Mã số';
			break;
		case 'Calculate Shipping':
			$translation = 'Tính phí vận chuyển';
			break;
		case 'Update Totals':
			$translation = 'Cập nhật chi phí';
			break;
		case 'Select a country&hellip;':
		case 'Select a country...':
			$translation = 'Chọn quốc gia...';
			break;
		case 'State / county':
			$translation = 'Tỉnh / Thành Phố';
			break;
		case 'Default sorting':
			$translation = 'Sắp xếp mặc định';
			break;
		case 'Sort by popularity':
			$translation = 'Sắp xếp theo độ phổ biến';
			break;
		case 'Sort by average rating':
			$translation = 'Sắp xếp theo đánh giá trung bình';
			break;
		case 'Sort by newness':
			$translation = 'Sắp xếp theo mới nhất';
			break;
		case 'Sort by price: low to high':
			$translation = 'Sắp xếp theo giá thấp đến cao';
			break;
		case 'Sort by price: high to low':
			$translation = 'Sắp xếp theo giá cao đến thấp';
			break;
		case 'Price:':
			$translation = 'Giá:';
			break;
		case 'Filter':
			$translation = 'Lọc';
			break;
		case 'Hello %s%s%s (not %2$s? %sSign out%s)':
			$translation = 'Xin chào %s%s%s (không phải %2$s? %sĐăng xuất%s)';
			break;
		case 'From your account dashboard you can view your %1$srecent orders%2$s, manage your %3$sshipping and billing addresses%2$s and %4$sedit your password and account details%2$s.':
			$translation = 'Bạn có thể xem thông tin %1$scác đơn hàng gần đây%2$s, quản lý %3$sđịa chỉ nhận hàng và địa chỉ thanh toán%2$s, chỉnh sửa %4$smật khẩu và thông tin tài khoản%2$s.';
			break;
		case 'No order has been made yet.':
			$translation = 'Chưa có đơn hàng nào được tạo.';
			break;
		case 'Go Shop':
			$translation = 'Đến trang mua hàng';
			break;
		case 'Dashboard':
			$translation = 'Bảng điều khiển';
			break;
		case 'Orders':
			$translation = 'Đơn hàng';
			break;
		case 'Downloads':
			$translation = 'Tải về';
			break;
		case 'Addresses':
			$translation = 'Địa chỉ';
			break;
		case 'Account Details';
			$translation = 'Thông tin tài khoản';
			break;
		case 'Logout':
			$translation = 'Thoát';
			break;
		case 'No downloads available yet.':
			$translation = 'Không có tập tin để tải về.';
			break;
		case 'Shipping Address':
			$translation = 'Địa chỉ nhận hàng';
			break;
		case 'You have not set up this type of address yet.':
			$translation = 'Bạn chưa thiết lập loại địa chỉ này.';
			break;
		case 'First name':
			$translation = 'Tên';
			break;
		case 'Last name':
			$translation = 'Họ';
			break;
		case 'Password Change':
			$translation = 'Đổi mật khẩu';
			break;
		case 'Current Password (leave blank to leave unchanged)':
			$translation = 'Mật khẩu hiện tại (để trống nếu bạn không muốn thay đổi)';
			break;
		case 'New Password (leave blank to leave unchanged)':
			$translation = 'Mật khẩu mới (để trống nếu bạn không muốn thay đổi)';
			break;
		case 'Confirm New Password':
			$translation = 'Nhập lại mật khẩu mới';
			break;
		case 'Save changes':
			$translation = 'Lưu thay đổi';
			break;
		case 'No products were found matching your selection.':
			$translation = 'Không tìm thấy sản phẩm.';
			break;
		case 'Out of stock':
			$translation = 'Hết hàng';
			break;
		case '%s in stock':
			$translation = 'Còn %s sản phẩm';
			break;
		case 'In stock':
			$translation = 'Còn hàng';
			break;
		case 'Only %s left in stock':
			$translation = 'Chỉ còn %s sản phẩm trong kho';
			break;
		case '(also available on backorder)':
			$translation = '(bạn cũng có thể đặt hàng trước)';
			break;
		case 'Có thể đặt hàng trước':
			$translation = '';
			break;
		case 'Please select a rating':
			$translation = 'Xin vui lòng chọn đánh giá';
			break;
		case 'Back to cart page':
			$translation = 'Cập nhật giỏ hàng';
			break;
		case 'Buy now':
			$translation = 'Mua nhanh';
			break;
		case 'Fast order, without adding products to cart.':
			$translation = 'Mua hàng nhanh không cần thêm sản phẩm vào giỏ hàng.';
			break;
		case 'Fast order':
			$translation = 'Mua hàng nhanh';
			break;
		case 'Put order':
			$translation = 'Đặt hàng';
			break;
		case 'Checkout is not available whilst your cart is empty.':
			$translation = 'Bạn không thể thanh toán khi giỏ hàng đang trống.';
			break;
		case 'Apply coupon':
			$translation = 'Áp dụng mã giảm giá';
			break;
		case 'Update cart':
			$translation = 'Cập nhật giỏ hàng';
			break;
		case 'Proceed to checkout':
			$translation = 'Tiến hành thanh toán';
			break;
		case 'Billing details':
			$translation = 'Thông tin thanh toán';
			break;
		case 'Additional information':
			$translation = 'Thông tin thêm';
			break;
		case 'Order notes':
			$translation = 'Ghi chú đơn hàng';
			break;
		case 'Notes about your order, e.g. special notes for delivery.':
			$translation = 'Mô tả về đơn hàng của bạn, ví dụ như ghi chú thông tin giao và nhận hàng.';
			break;
		case 'Company name':
			$translation = 'Tên công ty';
			break;
		case 'Street address':
			$translation = 'Địa chỉ đường phố';
			break;
		case '%s is a required field.':
			$translation = '%s là mục bắt buộc.';
			break;
		case 'Billing %s':
			$translation = 'Thanh toán %s';
			break;
		case 'House number and street name':
			$translation = 'Số nhà và tên đường';
			break;
		case 'Order number:':
			$translation = 'Số hóa đơn:';
			break;
		case 'Payment method:':
			$translation = 'Phương thức thanh toán:';
			break;
		case 'Order details':
			$translation = 'Thông tin đơn hàng';
			break;
		case 'Email:':
			$translation = 'Địa chỉ email:';
			break;
		case 'Phone:':
			$translation = 'Số điện thoại:';
			break;
		case 'Hello %1$s (not %1$s? <a href="%2$s">Log out</a>)':
			$translation = 'Xin chào %1$s (không phải %1$s? <a href="%2$s">Đăng xuất</a>)';
			break;
		case 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a> and <a href="%3$s">edit your password and account details</a>.':
			$translation = 'Bạn có thể quản lý <a href="%1$s">các đơn hàng gần đây</a>, quản lý <a href="%2$s">địa chỉ thanh toán cũng như địa chỉ giao nhận hàng</a> và <a href="%3$s">thay đổi mật khẩu hoặc cập nhật thông tin cá nhân</a> trên bảng điều khiển tài khoản của mình.';
			break;
		case 'Go shop':
			$translation = 'Đến trang sản phẩm';
			break;
		case 'Account details':
			$translation = 'Thông tin tài khoản';
			break;

		case 'Password change':
			$translation = 'Thay đổi mật khẩu';
			break;
		case 'Current password (leave blank to leave unchanged)':
			$translation = 'Mật khẩu hiện tại (để trống nếu bạn không muốn thay đổi)';
			break;
		case 'New password (leave blank to leave unchanged)':
			$translation = 'Mật khẩu mới (để trống nếu bạn không muốn thay đổi)';
			break;
		case 'Confirm new password':
			$translation = 'Xác nhận mật khẩu';
			break;
		case 'Are you sure you want to log out? <a href="%s">Confirm and log out</a>':
			$translation = 'Bạn có thật sự muốn đăng xuất hay không? <a href=\"%s\">Xác nhận đăng xuất</a>.';
			break;
		case 'Error: %s.':
			$translation = 'Lỗi: %s.';
			break;
		case 'Error:':
			$translation = 'Lỗi:';
			break;
		case 'Cart totals':
			$translation = 'Tổng giỏ hàng:';
			break;
	}

	return $translation;
}

function hocwp_theme_translation_gettext_with_context_woocommerce( $translation, $text, $context, $domain = 'default' ) {
	switch ( $text ) {
		case 'Notes about your order, e.g. special notes for delivery.':
			$translation = 'Mô tả về đơn hàng của bạn, ví dụ như ghi chú thông tin giao và nhận hàng.';
			break;
		case 'Street address':
			$translation = 'Địa chỉ nhà';
			break;
		case 'Apartment, suite, unit etc. (optional)':
			$translation = 'Địa chỉ cụ thể, ví dụ căn hộ, số phòng,...';
			break;
		case 'Qty':
			$translation = 'Số lượng';
			break;
		case '%s is a required field.':
			$translation = '%s mà mục bắt buộc.';
			break;
		case 'Billing %s':
			$translation = 'Địa chỉ đơn hàng %s';
			break;
		case 'Shipping %s':
			$translation = 'Địa chỉ nhận hàng %s';
			break;
		case 'Home':
			$translation = 'Trang chủ';
			break;
		case 'Payment method:':
			$translation = 'Phương thức thanh toán:';
			break;
	}

	return $translation;
}

function hocwp_theme_translation_ngettext_woocommerce( $translation, $single, $plural, $number, $domain = 'default' ) {
	$translations = get_translations_for_domain( $domain );
	$translation  = $translations->translate_plural( $single, $plural, $number );
	switch ( $translation ) {
		case '%s has been added to your cart.':
			$translation = '%s đã được thêm vào giỏ hàng thành công.';
			break;
		case '%s reviews for %s%s%s':
		case '%s review for %s%s%s':
			$translation = '%s nhận xét cho %s%s%s';
			break;
		case '%s review for %s':
		case '%s reviews for %s':
			$translation = '%s nhận xét cho %s';
			break;
		case '%s customer reviews':
		case '%s customer review':
			$translation = '%s nhận xét';
			break;
		case '%s for %s items';
		case '%s for %s item':
			$translation = '%s cho %s sản phẩm';
			break;
		case 'Shipping':
			$translation = 'Phí vận chuyển';
			break;
		case 'Home':
			$translation = 'Trang chủ';
			break;
		case 'Payment method:':
			$translation = 'Phương thức thanh toán:';
			break;
	}

	return $translation;
}

add_filter( 'gettext', 'hocwp_theme_translation_gettext_woocommerce', 11, 2 );
add_filter( 'gettext_with_context', 'hocwp_theme_translation_gettext_with_context_woocommerce', 11, 3 );
add_filter( 'hocwp_theme_translation_ngettext', 'hocwp_theme_translation_ngettext_woocommerce', 10, 4 );