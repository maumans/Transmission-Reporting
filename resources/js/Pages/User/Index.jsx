import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import {
    Box,
    Button,
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    Paper,
    Dialog,
    DialogTitle,
    DialogContent,
    DialogActions,
    TextField,
    FormControl,
    InputLabel,
    Select,
    MenuItem,
    IconButton,
} from '@mui/material';
import { Edit as EditIcon, Delete as DeleteIcon } from '@mui/icons-material';

export default function Index({ users, roles }) {
    const [open, setOpen] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        roles: [],
    });

    const handleOpen = (user = null) => {
        if (user) {
            setSelectedUser(user);
            setFormData({
                name: user.name,
                email: user.email,
                password: '',
                roles: user.roles.map(role => role.name),
            });
        } else {
            setSelectedUser(null);
            setFormData({
                name: '',
                email: '',
                password: '',
                roles: [],
            });
        }
        setOpen(true);
    };

    const handleClose = () => {
        setOpen(false);
        setSelectedUser(null);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (selectedUser) {
            router.put(route('user.update', selectedUser.id), formData, {
                onSuccess: () => {
                    handleClose();
                },
            });
        } else {
            router.post(route('user.store'), formData, {
                onSuccess: () => {
                    handleClose();
                },
            });
        }
        handleClose();
    };

    const handleDelete = (userId) => {
        if (window.confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
            window.axios.delete(route('user.destroy', userId));
        }
    };

    return (
        <AdminLayout>
            <Head title="Gestion des utilisateurs" />
            <Box sx={{ p: 3 }}>
                <Box sx={{ display: 'flex', justifyContent: 'space-between', mb: 3 }}>
                    <h1>Gestion des utilisateurs</h1>
                    <Button variant="contained" color="primary" onClick={() => handleOpen()}>
                        Ajouter un utilisateur
                    </Button>
                </Box>

                <TableContainer component={Paper}>
                    <Table>
                        <TableHead>
                            <TableRow>
                                <TableCell>Nom</TableCell>
                                <TableCell>Email</TableCell>
                                <TableCell>Rôles</TableCell>
                                <TableCell>Actions</TableCell>
                            </TableRow>
                        </TableHead>
                        <TableBody>
                            {users.map((user) => (
                                <TableRow key={user.id}>
                                    <TableCell>{user.name}</TableCell>
                                    <TableCell>{user.email}</TableCell>
                                    <TableCell>
                                        {user.roles.map(role => role.name).join(', ')}
                                    </TableCell>
                                    <TableCell>
                                        <IconButton onClick={() => handleOpen(user)}>
                                            <EditIcon />
                                        </IconButton>
                                        <IconButton onClick={() => handleDelete(user.id)}>
                                            <DeleteIcon />
                                        </IconButton>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </TableContainer>

                <Dialog open={open} onClose={handleClose}>
                    <DialogTitle>
                        {selectedUser ? 'Modifier un utilisateur' : 'Ajouter un utilisateur'}
                    </DialogTitle>
                    <form onSubmit={handleSubmit}>
                        <DialogContent>
                            <TextField
                                autoFocus
                                margin="dense"
                                label="Nom"
                                type="text"
                                fullWidth
                                value={formData.name}
                                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                required
                            />
                            <TextField
                                margin="dense"
                                label="Email"
                                type="email"
                                fullWidth
                                value={formData.email}
                                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                required
                            />
                            <TextField
                                margin="dense"
                                label="Mot de passe"
                                type="password"
                                fullWidth
                                value={formData.password}
                                onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                                required={!selectedUser}
                            />
                            <FormControl fullWidth margin="dense">
                                <InputLabel>Rôles</InputLabel>
                                <Select
                                    multiple
                                    value={formData.roles}
                                    onChange={(e) => setFormData({ ...formData, roles: e.target.value })}
                                    required
                                >
                                    {roles.map((role) => (
                                        <MenuItem key={role.id} value={role.name}>
                                            {role.name}
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                        </DialogContent>
                        <DialogActions>
                            <Button onClick={handleClose}>Annuler</Button>
                            <Button type="submit" variant="contained" color="primary">
                                {selectedUser ? 'Modifier' : 'Ajouter'}
                            </Button>
                        </DialogActions>
                    </form>
                </Dialog>
            </Box>
        </AdminLayout>
    );
} 