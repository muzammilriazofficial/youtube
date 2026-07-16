<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Database;

class StaticController extends Controller
{
    public function about(): Response
    {
        return $this->view('guest.static-page', [
            'title' => 'About Us',
            'pageTitle' => 'About Us',
            'content' => 'About our YouTube clone platform.',
        ]);
    }

    public function contact(): Response
    {
        return $this->view('guest.static-page', [
            'title' => 'Contact Us',
            'pageTitle' => 'Contact Us',
            'content' => 'Get in touch with us.',
            'showContactForm' => true,
        ]);
    }

    public function submitContact(): Response
    {
        $errors = $this->validate([
            'name' => 'required|max:100',
            'email' => 'required|email',
            'subject' => 'required|max:200',
            'message' => 'required',
        ]);

        if (!empty($errors)) {
            return $this->withInput()->respondWithError('Please fix the validation errors.');
        }

        $data = $this->request->only(['name', 'email', 'subject', 'message']);

        Database::getInstance()->table('contact_messages')->insert([
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->session->flash('success', 'Your message has been sent. We will get back to you soon.');
        return $this->redirect('/contact');
    }

    public function privacy(): Response
    {
        return $this->view('guest.static-page', [
            'title' => 'Privacy Policy',
            'pageTitle' => 'Privacy Policy',
            'content' => 'Privacy policy content.',
        ]);
    }

    public function terms(): Response
    {
        return $this->view('guest.static-page', [
            'title' => 'Terms of Service',
            'pageTitle' => 'Terms of Service',
            'content' => 'Terms of service content.',
        ]);
    }
}
