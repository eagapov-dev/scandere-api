<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Scandere AI Store API",
 *     description="REST API for digital products e-commerce platform. Sell and manage digital documents (PDFs, Excel, Word) with secure downloads, payments via Stripe, and comprehensive admin panel.",
 *     @OA\Contact(
 *         email="team@scandere.info",
 *         name="Scandere Support"
 *     )
 * )
 *
 * @OA\Server(
 *     url="https://api.scandereai.store",
 *     description="Production API Server"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local Development Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum",
 *     description="Enter your Sanctum token (obtained from login). Format: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication endpoints (register, login, logout, password reset)"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="Browse and manage digital products"
 * )
 *
 * @OA\Tag(
 *     name="Cart",
 *     description="Shopping cart management"
 * )
 *
 * @OA\Tag(
 *     name="Payments",
 *     description="Stripe checkout and payment processing"
 * )
 *
 * @OA\Tag(
 *     name="Comments",
 *     description="Product reviews and Q&A"
 * )
 *
 * @OA\Tag(
 *     name="Newsletter",
 *     description="Email subscription management"
 * )
 *
 * @OA\Tag(
 *     name="Contact",
 *     description="Contact form submissions"
 * )
 *
 * @OA\Tag(
 *     name="Admin - Products",
 *     description="Admin endpoints for product management"
 * )
 *
 * @OA\Tag(
 *     name="Admin - Orders",
 *     description="Admin endpoints for order management"
 * )
 *
 * @OA\Tag(
 *     name="Admin - Subscribers",
 *     description="Admin endpoints for newsletter subscriber management"
 * )
 *
 * @OA\Tag(
 *     name="Admin - Comments",
 *     description="Admin endpoints for comment moderation"
 * )
 *
 * @OA\Tag(
 *     name="Admin - Messages",
 *     description="Admin endpoints for contact message management"
 * )
 */
abstract class Controller {}
