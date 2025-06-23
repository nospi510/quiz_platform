from flask_login import UserMixin
from . import db

class User(db.Model, UserMixin):
    __tablename__ = 'user'
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(50), unique=True, nullable=False)
    password = db.Column(db.String(255), nullable=False)
    extension = db.Column(db.String(10), unique=True, nullable=False)
    calls_made = db.Column(db.Integer, default=0)
    is_admin = db.Column(db.Boolean, default=False)