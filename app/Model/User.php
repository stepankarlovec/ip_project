<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

class User implements Nette\Security\Authenticator
{
    public function __construct(private Nette\Database\Explorer $db, private Nette\Security\Passwords $passwords) {
    }

    public function get($id): ?Nette\Database\Table\ActiveRow
    {
        return $this->db->table('employee')
            ->where('id', $id)
            ->fetch();
    }

    public function all(): Nette\Database\Table\Selection
    {
        return $this->db->table('employee')
            ->order('id');
    }

    public function authenticate(string $username, string $password) : SimpleIdentity
    {
        $row = $this->db->table('employee')
            ->where('login', $username)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('User with this login not found.');
        } elseif (!$this->passwords->verify($password, $row['password'])) {
            throw new Nette\Security\AuthenticationException('Incorrect password.');
        } elseif ($this->passwords->needsRehash($row['password'])) {
            $row->update([
                'password' => $this->passwords->hash($password),
            ]);
        }

        $arr = $row->toArray();

        unset($arr['password']);

        $role = $row['admin'];

        return new SimpleIdentity($row['employee_id'], $role, $arr);
    }
}