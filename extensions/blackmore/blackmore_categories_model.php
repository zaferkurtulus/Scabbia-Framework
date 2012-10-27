<?php	class blackmoreCategoriesModel extends model {		public function insert($uInput) {			$tTime = time::toDb(time());			return $this->db->createQuery()								->setTable('categories')								->setFields($uInput)								->addField('categoryid', string::generateUuid())								->addField('createdate', $tTime)								->addField('updatedate', $tTime)								->setReturning('categoryid')								->insert()								->execute();		}		public function update($uCategoryId, $uInput) {			$tTime = time::toDb(time());			return $this->db->createQuery()								->setTable('categories')								->setFields($uInput)								->addField('updatedate', $tTime)								->addParameter('categoryid', $uCategoryId)								->setWhere('categoryid=:categoryid')								->andWhere('deletedate IS NULL')								->setLimit(1)								->update()								->execute();		}		public function deletePhysically($uCategoryId) {			return $this->db->createQuery()								->setTable('categories')								->addParameter('categoryid', $uCategoryId)								->setWhere('categoryid=:categoryid')								->setLimit(1)								->delete()								->execute();		}		public function delete($uCategoryId) {			$tTime = time::toDb(time());			return $this->db->createQuery()								->setTable('categories')								->addField('deletedate', $tTime)								->addParameter('categoryid', $uCategoryId)								->setWhere('categoryid=:categoryid')								->andWhere('deletedate IS NULL')								->setLimit(1)								->update()								->execute();		}		public function deleteBySlug($uSlug) {			$tTime = time::toDb(time());			return $this->db->createQuery()								->setTable('categories')								->addField('deletedate', $tTime)								->addParameter('slug', $uSlug)								->setWhere('slug=:slug')								->andWhere('deletedate IS NULL')								->setLimit(1)								->update()								->execute();		}		public function get($uCategoryId) {			return $this->db->createQuery()								->setTable('categories')								->addField('*')								->addParameter('categoryid', $uCategoryId)								->setWhere('categoryid=:categoryid')								->andWhere('deletedate IS NULL')								->setLimit(1)								->get()								->row();		}		public function getBySlug($uSlug) {			return $this->db->createQuery()								->setTable('categories')								->addField('*')								->addParameter('slug', $uSlug)								->setWhere('slug=:slug')								->andWhere('deletedate IS NULL')								->setLimit(1)								->get()								->row();		}		public function getAll() {			return $this->db->createQuery()								->setTable('categories')								->addField('*')								->setWhere('deletedate IS NULL')								->setOrderBy('createdate', 'DESC')								->get()								->all();		}		public function getAllByType($uType) {			return $this->db->createQuery()								->setTable('categories')								->addField('*')								->addParameter('type', $uType)								->setWhere('deletedate IS NULL')								->andWhere('type=:type')								->setOrderBy('createdate', 'DESC')								->get()								->all();		}		public function getAllAsPairs() {			$tQuery = $this->db->createQuery()								->setTable('categories')								->addField('*')								->setWhere('deletedate IS NULL')								->setOrderBy('createdate', 'DESC')								->get();			$tArray = array();			foreach($tQuery as $tRow) {				$tArray[$tRow['categoryid']] = $tRow;			}			$tQuery->close();			return $tArray;		}		public function getAsPairs($uType) {			$tQuery = $this->db->createQuery()								->setTable('categories')								->addField('*')								->addParameter('type', $uType)								->setWhere('deletedate IS NULL')								->andWhere('type=:type')								->setOrderBy('createdate', 'DESC')								->get();			$tArray = array();			foreach($tQuery as $tRow) {				$tArray[$tRow['categoryid']] = $tRow;			}			$tQuery->close();			return $tArray;		}		public function count() {			return $this->db->calculate('categories', 'COUNT', '*', 'deletedate IS NULL');		}	}?>