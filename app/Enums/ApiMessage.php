<?php

namespace App\Enums;

enum ApiMessage: string
{
    case USER_CREATED   = 'User has been created successfully.';
    case USER_UPDATED   = 'User updated successfully.';
    case USER_NOT_FOUND = 'User not found.';
    case LOGIN_SUCCESS  = 'Login successful.';
    case LOGIN_FAILED   = 'Invalid credentials.';
    case LOGOUT_SUCCESS = 'Logout successful.';
    case UNAUTHORIZED   = 'You are not authorized to perform this action.';
    case USER_STATUS_UPDATED = 'User status updated successfully.';
    case USER_DELETED = 'User deleted successfully.';
    case USER_FETCHED = 'User fetched successfully.';
    case STORE_CREATED = 'Store created successfully.';
    case STORE_UPDATED = 'Store updated successfully.';
    case STORE_DELETED = 'Store deleted successfully.';
    case STORE_NOT_FOUND = 'Store not found.';
    case CATEGORY_CREATED = 'Category created successfully.';
    case CATEGORY_UPDATED = 'Category updated successfully.';
    case CATEGORY_DELETED =  'Category deleted successfully.';
    case PRODUCT_CREATED  = 'Product created successfully.';
    case PRODUCT_UPDATED  = 'Product updated successfully.';
    case PRODUCT_DELETED  = 'Product deleted successfully.';
    case PRODUCT_FETCHED  = 'Product fetched successfully.';
    case PRODUCTS_FETCHED = 'Products fetched successfully.';
    case OFFER_CREATED = 'Offer created successfully.';
    case OFFER_UPDATED = 'Offer updated successfully.';
    case OFFER_DELETED = 'Offer deleted successfully.';
    case NOTIFICATION_LIST    = 'Notifications retrieved successfully.';
    case NOTIFICATION_CREATED = 'Notification created successfully.';
    case NOTIFICATION_UPDATED = 'Notification updated successfully.';
    case NOTIFICATION_DELETED = 'Notification deleted successfully.';
}
